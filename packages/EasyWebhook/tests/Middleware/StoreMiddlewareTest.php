<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\ArrayWebhookResultStoreStub;
use EonX\EasyWebhook\Webhook;

final class StoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testProcess(): void
    {
        $webhook = new Webhook();
        $store = new ArrayWebhookResultStoreStub();
        $middleware = new StoreMiddleware($store);

        $result = $this->process($middleware, $webhook);

        self::assertCount(1, $store->getResults());
        self::assertEquals(\spl_object_hash($result), \spl_object_hash($store->getResults()[0]));
        self::assertEquals(\spl_object_hash($webhook), \spl_object_hash($store->getResults()[0]->getWebhook()));
    }
}
