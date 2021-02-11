<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class ResetStoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testProcess(): void
    {
        $webhook = Webhook::fromArray([]);
        $result = new WebhookResult($webhook);
        $store = new ArrayStore($this->getRandomGenerator());
        $resultStore = new ArrayResultStore($this->getRandomGenerator());

        $store->store($webhook);
        $resultStore->store($result);
        $middleware = new ResetStoreMiddleware($store, $resultStore);

        $this->process($middleware, $webhook);

        self::assertEmpty($store->getWebhooks());
        self::assertEmpty($resultStore->getResults());
    }
}
