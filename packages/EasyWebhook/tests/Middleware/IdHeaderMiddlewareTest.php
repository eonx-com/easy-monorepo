<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultStoreInterface;
use EonX\EasyWebhook\Middleware\IdHeaderMiddleware;
use EonX\EasyWebhook\Stores\NullWebhookResultStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\ArrayWebhookResultStoreStub;
use EonX\EasyWebhook\Webhook;

final class IdHeaderMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestProcess(): iterable
    {
        yield 'no id' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertNull($webhookResult->getWebhook()->getHttpClientOptions()['headers'] ?? null);
            },
            null,
            new NullWebhookResultStore(),
        ];

        yield 'id from store' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                $headers = $webhookResult->getWebhook()
                    ->getHttpClientOptions()['headers'] ?? [];

                self::assertArrayHasKey(WebhookInterface::HEADER_ID, $headers);
                self::assertEquals('webhook-id', $headers[WebhookInterface::HEADER_ID]);
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
        ?WebhookResultStoreInterface $store = null
    ): void {
        $middleware = new IdHeaderMiddleware($store ?? new ArrayWebhookResultStoreStub(), $idHeader);

        $test($this->process($middleware, $webhook));
    }
}
