<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use Doctrine\DBAL\Connection;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalStore;
use EonX\EasyWebhook\Tests\AbstractStoreTestCase;
use EonX\EasyWebhook\Webhook;
use Illuminate\Database\Capsule\Manager;

final class DoctrineDbalStoreFromIlluminateDatabaseTest extends AbstractStoreTestCase
{
    public function testStore(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $id = 'my-id';
        $store = new DoctrineDbalStore(self::getRandomGenerator(), $conn, $this->getDataCleaner());
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)->id($id);

        // Save new result with set id
        $store->store($webhook);
        // Update result
        $store->store($webhook);

        self::assertInstanceOf(WebhookInterface::class, $store->find($id));
    }

    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->doctrineDbal === null) {
            $dbManager = new Manager();
            $dbManager->addConnection([
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            $this->doctrineDbal = $dbManager->getConnection()
                ->getDoctrineConnection();
        }

        return $this->doctrineDbal;
    }
}
