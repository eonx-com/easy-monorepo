<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Doctrine\Store;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Doctrine\Store\DoctrineDbalResultStore;

final class DoctrineDbalResultStoreTest extends AbstractDoctrineDbalStoreTestCase
{
    public function testStore(): void
    {
        $store = new DoctrineDbalResultStore(
            self::getRandomGenerator(),
            $this->getDoctrineDbalConnection(),
            $this->getDataCleaner()
        );
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)
            ->id('webhook-id');
        $result = new WebhookResult($webhook);

        $store->store($result);

        self::assertNotEmpty($result->getId());
    }
}
