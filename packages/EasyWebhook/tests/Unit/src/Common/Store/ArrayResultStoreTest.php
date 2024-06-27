<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Store;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Store\ArrayResultStore;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class ArrayResultStoreTest extends AbstractUnitTestCase
{
    public function testSanity(): void
    {
        $resultStore = new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());
        $result = $resultStore->store(new WebhookResult(Webhook::fromArray([])));
        $results = $resultStore->getResults();
        $resultStore->reset();
        $resetResults = $resultStore->getResults();

        self::assertSame($result, \reset($results));
        self::assertEmpty($resetResults);
    }
}
