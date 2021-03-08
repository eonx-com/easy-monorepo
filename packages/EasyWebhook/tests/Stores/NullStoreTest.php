<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Stores\NullStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;

final class NullStoreTest extends AbstractTestCase
{
    public function testSanity(): void
    {
        $webhook = Webhook::fromArray([]);
        $store = new NullStore();

        self::assertNull($store->find('always-null'));
        self::assertEquals('webhook-id', $store->generateWebhookId());
        self::assertSame($webhook, $store->store($webhook));
    }
}
