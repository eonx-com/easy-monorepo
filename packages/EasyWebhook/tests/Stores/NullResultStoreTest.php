<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Stores\NullResultStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class NullResultStoreTest extends AbstractTestCase
{
    public function testSanity(): void
    {
        $result = new WebhookResult(Webhook::fromArray([]));
        $store = new NullResultStore();

        self::assertSame($result, $store->store($result));
    }
}
