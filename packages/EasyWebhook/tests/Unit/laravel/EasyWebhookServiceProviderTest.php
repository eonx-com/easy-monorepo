<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Laravel;

use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId;
use EonX\EasyWebhook\Common\Client\WebhookClientInterface;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookSecretException;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\HttpClient\AllowedHostsHttpClient;
use EonX\EasyWebhook\Common\Middleware\SendWebhookMiddleware;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Laravel\EasyWebhookServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class EasyWebhookServiceProviderTest extends AbstractLaravelTestCase
{
    /**
     * @see testSendWebhookMiddlewareFollowsSsrfProtectionConfig
     */
    public static function provideSendWebhookMiddlewareData(): iterable
    {
        yield 'SSRF protection enabled by default' => [null, WebhookHttpClientOptionNotAllowedException::class];

        yield 'SSRF protection disabled' => [
            [
                'easy-webhook.ssrf_protection.enabled' => false,
            ],
            WebhookRequestFailedException::class,
        ];
    }

    public function testDefaultSignatureSecretIsAcceptedWhenSignatureIsDisabled(): void
    {
        $app = $this->getApplication();

        self::assertSame(
            EasyWebhookServiceProvider::DEFAULT_SIGNATURE_SECRET,
            \config('easy-webhook.signature.secret')
        );
        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }

    public function testDefaultSignatureSecretIsRejectedWhenSignatureIsEnabled(): void
    {
        $this->expectException(InvalidWebhookSecretException::class);
        $this->expectExceptionMessage('Set your own value in the "easy-webhook.signature.secret" config.');

        $this->getApplication(null, [
            'easy-webhook.signature.enabled' => true,
            'easy-webhook.signature.secret' => EasyWebhookServiceProvider::DEFAULT_SIGNATURE_SECRET,
        ]);
    }

    public function testOwnSignatureSecretIsAcceptedWhenSignatureIsEnabled(): void
    {
        $app = $this->getApplication(null, [
            'easy-webhook.signature.enabled' => true,
            'easy-webhook.signature.secret' => 'own-secret',
        ]);

        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }

    public function testSanity(): void
    {
        $app = $this->getApplication();

        self::assertInstanceOf(WebhookClientInterface::class, $app->make(WebhookClientInterface::class));
    }

    /**
     * @param class-string<\Throwable> $expectedThrowable
     */
    #[DataProvider('provideSendWebhookMiddlewareData')]
    public function testSendWebhookMiddlewareFollowsSsrfProtectionConfig(
        ?array $config,
        string $expectedThrowable,
    ): void {
        $middleware = $this->getApplication(null, $config)
            ->make(SendWebhookMiddleware::class);
        // Nothing listens on this port, so a request that is not rejected fails to connect
        $webhook = Webhook::fromArray([
            'url' => 'http://127.0.0.1:1',
        ])->httpClientOptions([
            'proxy' => 'http://127.0.0.1:1',
        ]);

        $result = $middleware->process($webhook, new Stack([$middleware]));

        self::assertInstanceOf($expectedThrowable, $result->getThrowable());
    }

    public function testSsrfAllowedHostsAreWiredIntoHttpClient(): void
    {
        $app = $this->getApplication(null, [
            // Request limits are on by default in 7.x and would wrap the SSRF client, keep it outermost
            'easy-webhook.request_limits.enabled' => false,
            'easy-webhook.ssrf_protection.allowed_hosts' => [' Api.AHI.UAT.Example ', ''],
        ]);

        $httpClient = $app->make(ConfigServiceId::HttpClient->value);
        self::assertInstanceOf(AllowedHostsHttpClient::class, $httpClient);

        $response = $httpClient->request('GET', 'https://api.ahi.uat.example/webhooks', [
            'resolve' => ['api.ahi.uat.example' => '10.24.80.5'],
        ]);
        $response->cancel();
        self::assertInstanceOf(ResponseInterface::class, $response);

        $this->expectException(TransportExceptionInterface::class);

        $httpClient->request('GET', 'https://other.example/webhooks', [
            'resolve' => ['other.example' => '10.24.80.5'],
        ]);
    }
}
