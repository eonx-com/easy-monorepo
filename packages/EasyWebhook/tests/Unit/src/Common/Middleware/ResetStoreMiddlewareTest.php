<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Middleware\ResetStoreMiddleware;
use EonX\EasyWebhook\Common\Store\ArrayResultStore;
use EonX\EasyWebhook\Common\Store\ArrayStore;

final class ResetStoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testProcess(): void
    {
        $webhook = Webhook::fromArray([]);
        $result = new WebhookResult($webhook);
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $resultStore = new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());

        $store->store($webhook);
        $resultStore->store($result);
        $middleware = new ResetStoreMiddleware($store, $resultStore);

        $this->process($middleware, $webhook);

        self::assertEmpty($store->getWebhooks());
        self::assertEmpty($resultStore->getResults());
    }
}
