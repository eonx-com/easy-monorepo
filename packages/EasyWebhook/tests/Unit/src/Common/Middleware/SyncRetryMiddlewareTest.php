<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Common\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Common\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Common\Middleware\SyncRetryMiddleware;
use EonX\EasyWebhook\Common\Stack\Stack;
use EonX\EasyWebhook\Common\Store\ArrayResultStore;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use EonX\EasyWebhook\Common\Strategy\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Tests\Stub\Middleware\MiddlewareStub;
use EonX\EasyWebhook\Tests\Stub\Stack\StackStub;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;

final class SyncRetryMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testDoNotRetryIfAsyncEnabledOrMaxAttemptIsOne
     */
    public static function providerTestDoNotRetryIfAsyncEnabledOrMaxAttempt(): iterable
    {
        yield 'async enabled' => [true, 3];

        yield 'max attempt is one' => [false, 1];
    }

    #[DataProvider('providerTestDoNotRetryIfAsyncEnabledOrMaxAttempt')]
    public function testDoNotRetryIfAsyncEnabledOrMaxAttemptIsOne(bool $asyncEnabled, int $maxAttempt): void
    {
        $webhook = Webhook::create('https://eonx.com')->maxAttempt($maxAttempt);
        $resultsStore = new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());

        $stack = new StackStub(new Stack([
            new SyncRetryMiddleware($resultsStore, new MultiplierWebhookRetryStrategy(), $asyncEnabled),
            new MiddlewareStub(null, new Exception('my-message')),
        ]));

        $result = $stack
            ->next()
            ->process($webhook, $stack);

        $expectedStackCalls = [
            SyncRetryMiddleware::class => 1,
            MiddlewareStub::class => 1,
        ];

        self::assertFalse($result->isSuccessful());
        self::assertEquals($expectedStackCalls, $stack->getCalls());
    }

    public function testRetryWhenResultNotSuccessful(): void
    {
        $webhook = Webhook::create('https://eonx.com')->maxAttempt(3);
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $resultsStore = new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());

        $stack = new StackStub(new Stack([
            new StoreMiddleware($store, $resultsStore),
            new StatusAndAttemptMiddleware(),
            new MethodMiddleware('POST'),
            new SyncRetryMiddleware($resultsStore, new MultiplierWebhookRetryStrategy(), false),
            new MiddlewareStub(null, new Exception('my-message')),
        ]));

        $result = $stack
            ->next()
            ->process($webhook, $stack);

        $expectedStackCalls = [
            StoreMiddleware::class => 1,
            StatusAndAttemptMiddleware::class => 1,
            MethodMiddleware::class => 1,
            SyncRetryMiddleware::class => 1,
            MiddlewareStub::class => 3,
        ];

        self::assertFalse($result->isSuccessful());
        self::assertCount(1, $store->getWebhooks());
        self::assertCount(3, $resultsStore->getResults());
        self::assertEquals($expectedStackCalls, $stack->getCalls());
    }
}
