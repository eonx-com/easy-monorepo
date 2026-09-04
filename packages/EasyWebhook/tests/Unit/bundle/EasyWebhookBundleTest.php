<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Bundle;

use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\HttpClient\AllowedHostsHttpClient;
use EonX\EasyWebhook\Common\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EasyWebhookBundleTest extends AbstractSymfonyTestCase
{
    private const ALLOWED_HOSTS_ENV = 'EASY_WEBHOOK_TEST_SSRF_ALLOWED_HOSTS';

    protected function tearDown(): void
    {
        \putenv(self::ALLOWED_HOSTS_ENV);

        parent::tearDown();
    }

    /**
     * @see testConfigAndDependenciesSanity
     */
    public static function provideConfigAndDependenciesSanityData(): iterable
    {
        yield 'Defaults' => [
            [],
            static function (ContainerInterface $container): void {
                self::assertFalse($container->hasParameter(ConfigParam::Secret->value));
                self::assertFalse($container->hasParameter(ConfigParam::SignatureHeader->value));
                self::assertFalse($container->has(ConfigServiceId::Signer->value));
                self::assertInstanceOf(
                    BodyFormatterMiddleware::class,
                    $container->get(BodyFormatterMiddleware::class)
                );
                self::assertInstanceOf(MethodMiddleware::class, $container->get(MethodMiddleware::class));
                self::assertSame([], $container->getParameter(ConfigParam::SsrfProtectionAllowedHosts->value));
            },
        ];

        yield 'Signature Defaults' => [
            [__DIR__ . '/../../Fixture/config/signature_defaults.php'],
            static function (ContainerInterface $container): void {
                self::assertNull($container->getParameter(ConfigParam::Secret->value));
                self::assertNull($container->getParameter(ConfigParam::SignatureHeader->value));
                self::assertInstanceOf(Rs256WebhookSigner::class, $container->get(ConfigServiceId::Signer->value));
            },
        ];

        yield 'Signature Custom' => [
            [__DIR__ . '/../../Fixture/config/signature_custom.php'],
            static function (ContainerInterface $container): void {
                self::assertEquals('my-secret', $container->getParameter(ConfigParam::Secret->value));
                self::assertEquals(
                    'X-My-Header',
                    $container->getParameter(ConfigParam::SignatureHeader->value)
                );
            },
        ];

        yield 'No default middleware' => [
            [__DIR__ . '/../../Fixture/config/no_default_middleware.php'],
            static function (ContainerInterface $container): void {
                self::assertFalse($container->has(BodyFormatterMiddleware::class));
            },
        ];
    }

    /**
     * @see testSsrfAllowedHostsFromEnv
     */
    public static function provideSsrfAllowedHostsFromEnvData(): iterable
    {
        yield 'Empty variable means empty allowlist' => ['', [], false];

        yield 'Single host' => ['api.ahi.uat.example', ['api.ahi.uat.example'], true];

        yield 'Padded mixed-case list' => [
            ' Api.AHI.UAT.Example , other.example',
            [' Api.AHI.UAT.Example ', ' other.example'],
            true,
        ];
    }

    /**
     * @param string[] $configs
     */
    #[DataProvider('provideConfigAndDependenciesSanityData')]
    public function testConfigAndDependenciesSanity(array $configs, callable $tests): void
    {
        $tests($this->getKernel($configs)->getContainer());
    }

    #[DataProvider('provideSsrfAllowedHostsFromEnvData')]
    public function testSsrfAllowedHostsFromEnv(string $envValue, array $expectedParameter, bool $expectAllowed): void
    {
        \putenv(\sprintf('%s=%s', self::ALLOWED_HOSTS_ENV, $envValue));

        $container = $this->getKernel([__DIR__ . '/../../Fixture/config/ssrf_protection_allowed_hosts_env.php'])
            ->getContainer();

        self::assertSame($expectedParameter, $container->getParameter(ConfigParam::SsrfProtectionAllowedHosts->value));

        $httpClient = $container->get(ConfigServiceId::HttpClient->value);
        self::assertInstanceOf(AllowedHostsHttpClient::class, $httpClient);

        $allowed = true;

        try {
            $httpClient->request('GET', 'https://api.ahi.uat.example/webhooks', [
                'resolve' => ['api.ahi.uat.example' => '10.24.80.5'],
            ])->cancel();
        } catch (TransportExceptionInterface) {
            $allowed = false;
        }

        self::assertSame($expectAllowed, $allowed);
        $this->assertRequestBlocked($httpClient, 'https://not-allowed.example/webhooks', [
            'resolve' => ['not-allowed.example' => '10.24.80.5'],
        ]);
    }

    private function assertRequestBlocked(HttpClientInterface $httpClient, string $url, array $options): void
    {
        $blocked = false;

        try {
            $httpClient->request('GET', $url, $options)
                ->cancel();
        } catch (TransportExceptionInterface) {
            $blocked = true;
        }

        self::assertTrue($blocked, "Expected request to {$url} to be blocked");
    }
}
