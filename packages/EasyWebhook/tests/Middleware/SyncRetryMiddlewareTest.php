<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Middleware\MethodMiddleware;
use EonX\EasyWebhook\Middleware\StatusAndAttemptMiddleware;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Middleware\SyncRetryMiddleware;
use EonX\EasyWebhook\RetryStrategies\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Stack;
use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\MiddlewareStub;
use EonX\EasyWebhook\Tests\Stubs\StackStub;
use EonX\EasyWebhook\Webhook;
use Exception;

final class SyncRetryMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDoNotRetryIfAsyncEnabledOrMaxAttemptIsOne
     */
    public static function providerTestDoNotRetryIfAsyncEnabledOrMaxAttempt(): iterable
    {
        yield 'async enabled' => [true, 3];

        yield 'max attempt is one' => [false, 1];
    }

    /**
     * @dataProvider providerTestDoNotRetryIfAsyncEnabledOrMaxAttempt
     */
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
