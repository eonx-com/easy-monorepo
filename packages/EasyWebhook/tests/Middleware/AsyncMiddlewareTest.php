<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Async\NullAsyncDispatcher;
use EonX\EasyWebhook\Exceptions\WebhookIdRequiredForAsyncException;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\ArrayStoreStub;
use EonX\EasyWebhook\Webhook;

final class AsyncMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestProcess(): iterable
    {
        yield 'enabled = false so just pass on the webhook' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertNull($webhookResult->getWebhook()->getId());
            },
            null,
            false,
        ];

        yield 'enabled = true but store does not set id on webhook' => [
            Webhook::fromArray([]),
            null,
            null,
            null,
            WebhookIdRequiredForAsyncException::class,
        ];

        yield 'enabled = true and store set id so it dispatches the webhook' => [
            Webhook::fromArray([]),
            static function (WebhookResultInterface $webhookResult): void {
                self::assertEquals('webhook-id', $webhookResult->getWebhook()->getId());
            },
            new ArrayStoreStub($this->getRandomGenerator(), 'webhook-id'),
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     *
     * @dataProvider providerTestProcess
     */
    public function testProcess(
        WebhookInterface $webhook,
        ?callable $test = null,
        ?StoreInterface $store = null,
        ?bool $enabled = null,
        ?string $expectedException = null
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $enabled = $enabled ?? true;
        $store = $store ?? new ArrayStoreStub($this->getRandomGenerator());
        $middleware = new AsyncMiddleware(new NullAsyncDispatcher(), $store, $enabled);

        $result = $this->process($middleware, $webhook);

        if ($test !== null) {
            $test($result);
        }
    }
}
