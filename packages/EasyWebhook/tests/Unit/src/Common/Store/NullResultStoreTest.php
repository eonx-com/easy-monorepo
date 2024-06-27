<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Store;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Store\NullResultStore;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class NullResultStoreTest extends AbstractUnitTestCase
{
    public function testSanity(): void
    {
        $result = new WebhookResult(Webhook::fromArray([]));
        $store = new NullResultStore();

        self::assertSame($result, $store->store($result));
    }
}
