<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\ArrayStoreStub;
use EonX\EasyWebhook\Webhook;

final class IdHeaderMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
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

    /**
     * @dataProvider providerTestProcess
     */
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
