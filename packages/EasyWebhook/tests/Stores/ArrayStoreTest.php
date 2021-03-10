<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;

final class ArrayStoreTest extends AbstractTestCase
{
    public function testSanity(): void
    {
        $store = new ArrayStore($this->getRandomGenerator());
        $webhook = $store->store(Webhook::fromArray([]));
        $findWebhook = $store->find((string)$webhook->getId());
        $webhooks = $store->getWebhooks();
        $store->reset();
        $resetWebhooks = $store->getWebhooks();

        self::assertSame($webhook, \reset($webhooks));
        self::assertSame($webhook, $findWebhook);
        self::assertEmpty($resetWebhooks);
    }
}
