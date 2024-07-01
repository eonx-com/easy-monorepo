<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Tests\Stub\Store\ArrayStoreStub;
use PHPUnit\Framework\Attributes\DataProvider;

final class IdHeaderMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'id from store' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(WebhookInterface::HEADER_ID, $headers);
                self::assertEquals('not-default-webhook-id', $headers[WebhookInterface::HEADER_ID]);
            },
        ];

        yield 'id with default header' => [
            Webhook::fromArray([
                'id' => 'my-id',
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(WebhookInterface::HEADER_ID, $headers);
                self::assertEquals('my-id', $headers[WebhookInterface::HEADER_ID]);
            },
        ];

        yield 'id with custom header' => [
            Webhook::fromArray([
                'id' => 'my-id',
            ]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey('X-My-Id', $headers);
                self::assertEquals('my-id', $headers['X-My-Id']);
            },
            'X-My-Id',
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        callable $test,
        ?string $idHeader = null,
        ?StoreInterface $store = null,
    ): void {
        // Fix webhook id
        $store ??= new ArrayStoreStub(self::getRandomGenerator(), 'not-default-webhook-id');
        $middleware = new IdHeaderMiddleware($store, $idHeader);

        $test($this->process($middleware, $webhook));
    }
}
