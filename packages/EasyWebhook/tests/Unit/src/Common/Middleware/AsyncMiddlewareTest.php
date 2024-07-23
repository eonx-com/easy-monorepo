<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Dispatcher\NullAsyncDispatcher;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Exception\WebhookIdRequiredForAsyncException;
use EonX\EasyWebhook\Common\Middleware\AsyncMiddleware;
use EonX\EasyWebhook\Common\Store\StoreInterface;
use EonX\EasyWebhook\Tests\Stub\Store\ArrayStoreStub;
use PHPUnit\Framework\Attributes\DataProvider;

final class AsyncMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
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
            new ArrayStoreStub(self::getRandomGenerator(), 'webhook-id'),
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable> $expectedException
     */
    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        ?callable $test = null,
        ?StoreInterface $store = null,
        ?bool $enabled = null,
        ?string $expectedException = null,
    ): void {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        $enabled ??= true;
        $store ??= new ArrayStoreStub(self::getRandomGenerator());
        $middleware = new AsyncMiddleware(new NullAsyncDispatcher(), $store, $enabled);

        $result = $this->process($middleware, $webhook);

        if ($test !== null) {
            $test($result);
        }
    }
}
