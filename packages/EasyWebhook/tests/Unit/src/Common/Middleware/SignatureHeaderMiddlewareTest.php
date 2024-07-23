<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\InvalidWebhookSecretException;
use EonX\EasyWebhook\Common\Middleware\SignatureHeaderMiddleware;
use EonX\EasyWebhook\Tests\Stub\Signer\SignerStub;
use PHPUnit\Framework\Attributes\DataProvider;

final class SignatureHeaderMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'Invalid secret' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_BODY_AS_STRING => 'not empty',
            ]),
            null,
            null,
            null,
            null,
            InvalidWebhookSecretException::class,
        ];

        yield 'No body to sign' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult, SignerStub $signer): void {
                self::assertNull($webhookResult->getWebhook()->getHttpClientOptions()['headers'] ?? null);
                self::assertNull($signer->getPayload());
                self::assertNull($signer->getSecret());
            },
        ];

        yield 'Sign with default secret + header' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_BODY_AS_STRING => 'not empty',
            ]),
            static function (WebhookResultInterface $webhookResult, SignerStub $signer): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(Webhook::HEADER_SIGNATURE, $headers);
                self::assertEquals('not empty', $signer->getPayload());
                self::assertEquals('my-secret', $signer->getSecret());
            },
            null,
            'my-secret',
        ];

        yield 'Sign with default secret and custom header' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_BODY_AS_STRING => 'not empty',
            ]),
            static function (WebhookResultInterface $webhookResult, SignerStub $signer): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('X-My-Signature', $headers);
                self::assertEquals('not empty', $signer->getPayload());
                self::assertEquals('my-secret', $signer->getSecret());
            },
            null,
            'my-secret',
            'X-My-Signature',
        ];

        yield 'Sign with webhook secret and custom header' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_BODY_AS_STRING => 'not empty',
                WebhookInterface::OPTION_SECRET => 'my-secret',
            ]),
            static function (WebhookResultInterface $webhookResult, SignerStub $signer): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('X-My-Signature', $headers);
                self::assertEquals('not empty', $signer->getPayload());
                self::assertEquals('my-secret', $signer->getSecret());
            },
            null,
            null,
            'X-My-Signature',
        ];

        yield 'Secret considered as empty' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_BODY_AS_STRING => 'not empty',
                WebhookInterface::OPTION_SECRET => '0',
            ]),
            static function (WebhookResultInterface $webhookResult, SignerStub $signer): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(WebhookInterface::HEADER_SIGNATURE, $headers);
                self::assertEquals('not empty', $signer->getPayload());
                self::assertEquals('0', $signer->getSecret());
            },
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable>|null $exceptedException
     */
    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        ?callable $test = null,
        ?string $signature = null,
        ?string $defaultSecret = null,
        ?string $defaultHeader = null,
        ?string $exceptedException = null,
    ): void {
        if ($exceptedException !== null) {
            $this->expectException($exceptedException);
        }

        $signer = new SignerStub($signature);
        $middleware = new SignatureHeaderMiddleware($signer, $defaultSecret, $defaultHeader);

        $result = $this->process($middleware, $webhook);

        if ($test !== null) {
            $test($result, $signer);
        }
    }
}
