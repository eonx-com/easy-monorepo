<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class StoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testProcess(): void
    {
        $webhook = new Webhook();
        $store = new ArrayStore($this->getRandomGenerator());
        $resultStore = new ArrayResultStore($this->getRandomGenerator());
        $middleware = new StoreMiddleware($store, $resultStore);

        $result = $this->process($middleware, $webhook);
        $webhooks = $store->getWebhooks();
        $results = $resultStore->getResults();
        $firstWebhook = \reset($webhooks);
        $firstResult = \reset($results);

        self::assertCount(1, $webhooks);
        self::assertCount(1, $results);
        self::assertSame($firstWebhook, $webhook);
        self::assertSame($firstResult, $result);
    }
}
