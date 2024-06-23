<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Bundle;

use EonX\EasyWebhook\Bundle\Enum\ConfigParam;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class EasyWebhookBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testConfigAndDependenciesSanity
     */
    public static function providerTestConfigAndDependenciesSanity(): iterable
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
     * @param string[] $configs
     */
    #[DataProvider('providerTestConfigAndDependenciesSanity')]
    public function testConfigAndDependenciesSanity(array $configs, callable $tests): void
    {
        $tests($this->getKernel($configs)->getContainer());
    }
}
