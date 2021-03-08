<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Stores\ArrayResultStore;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class ArrayResultStoreTest extends AbstractTestCase
{
    public function testSanity(): void
    {
        $resultStore = new ArrayResultStore($this->getRandomGenerator());
        $result = $resultStore->store(new WebhookResult(Webhook::fromArray([])));
        $results = $resultStore->getResults();
        $resultStore->reset();
        $resetResults = $resultStore->getResults();

        self::assertSame($result, \reset($results));
        self::assertEmpty($resetResults);
    }
}
