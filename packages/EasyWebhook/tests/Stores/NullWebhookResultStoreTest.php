<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Stores\NullWebhookResultStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class NullWebhookResultStoreTest extends AbstractTestCase
{
    public function testFind(): void
    {
        self::assertNull((new NullWebhookResultStore())->find('my-id'));
    }

    public function testStore(): void
    {
        $result = new WebhookResult(Webhook::fromArray([]));

        self::assertSame($result, (new NullWebhookResultStore())->store($result));
    }
}
