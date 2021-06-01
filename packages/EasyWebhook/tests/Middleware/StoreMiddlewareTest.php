<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\ShouldNotBeStoredWebhookResult;
use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class StoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestProcess(): iterable
    {
        yield 'Should store' => [1];

        yield 'Should not store' => [
            0,
            new ShouldNotBeStoredWebhookResult(Webhook::create('https://eonx.com')),
        ];
    }

    /**
     * @dataProvider providerTestProcess
     */
    public function testProcess(int $resultsCount, ?WebhookResultInterface $webhookResult = null): void
    {
        $webhook = new Webhook();
        $store = new ArrayStore($this->getRandomGenerator(), $this->getDataCleaner());
        $resultStore = new ArrayResultStore($this->getRandomGenerator(), $this->getDataCleaner());
        $middleware = new StoreMiddleware($store, $resultStore);

        $result = $this->process($middleware, $webhook, $webhookResult);
        $webhooks = $store->getWebhooks();
        $results = $resultStore->getResults();
        $firstWebhook = \reset($webhooks);
        $firstResult = \reset($results);

        self::assertCount(1, $webhooks);
        self::assertSame($firstWebhook, $webhook);
        self::assertCount($resultsCount, $results);

        if ($resultsCount > 0) {
            self::assertSame($firstResult, $result);
        }
    }
}
