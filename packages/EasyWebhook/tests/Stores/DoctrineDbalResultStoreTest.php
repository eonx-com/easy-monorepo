<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stores;

use EonX\EasyWebhook\Bridge\Doctrine\StatementProviders\SqliteStatementProvider;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\DoctrineDbalResultStore;
use EonX\EasyWebhook\Tests\AbstractStoreTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class DoctrineDbalResultStoreTest extends AbstractStoreTestCase
{
    public function testStore(): void
    {
        $store = new DoctrineDbalResultStore($this->getRandomGenerator(), $this->getDoctrineDbalConnection());
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD)
            ->id('webhook-id');
        $result = new WebhookResult($webhook);

        $store->store($result);

        self::assertNotEmpty($result->getId());
    }

    protected function setUp(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->connect();

        foreach (SqliteStatementProvider::migrateStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $conn = $this->getDoctrineDbalConnection();

        foreach (SqliteStatementProvider::rollbackStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        $conn->close();

        parent::tearDown();
    }
}
