<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Store;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class ArrayStoreTest extends AbstractUnitTestCase
{
    public function testSanity(): void
    {
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
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
