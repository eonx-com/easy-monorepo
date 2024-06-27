<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Store;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Store\NullStore;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class NullStoreTest extends AbstractUnitTestCase
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
