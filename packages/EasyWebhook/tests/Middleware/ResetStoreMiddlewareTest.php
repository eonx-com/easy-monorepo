<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Tests\Stubs\ResettableStoreStub;
use EonX\EasyWebhook\Webhook;

final class ResetStoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testProcess(): void
    {
        $store = new ResettableStoreStub();
        $middleware = new ResetStoreMiddleware($store);

        $this->process($middleware, new Webhook());

        self::assertEquals(1, $store->getCalls());
    }
}
