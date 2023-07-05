<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use EonX\EasyWebhook\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Webhook;

abstract class AbstractStoreTestCase extends AbstractTestCase
{
    protected ?Connection $doctrineDbal = null;

    private ?DbalStatementsProvider $stmtsProvider;

    protected function setUp(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->connect();

        $stmtsProvider = $this->getStmtsProvider();
        $stmtsProvider->extendWebhooksTable(static function (Table $table): void {
            $table->addColumn('extra_column', 'string', [
                'notNull' => false,
            ]);
        });

        foreach ($stmtsProvider->migrateStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $conn = $this->getDoctrineDbalConnection();

        foreach ($this->getStmtsProvider()->rollbackStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        $conn->close();

        parent::tearDown();
    }

    protected static function createWebhookForSendAfter(
        ?\DateTimeInterface $sendAfter = null,
        ?string $status = null,
    ): WebhookInterface {
        $webhook = Webhook::create('https://eonx.com', null, WebhookInterface::DEFAULT_METHOD);

        if ($sendAfter !== null) {
            $webhook->sendAfter($sendAfter);
        }

        if ($status !== null) {
            $webhook->status($status);
        }

        return $webhook;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        $this->doctrineDbal ??= DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);

        return $this->doctrineDbal;
    }

    private function getStmtsProvider(): DbalStatementsProvider
    {
        $this->stmtsProvider ??= new DbalStatementsProvider($this->getDoctrineDbalConnection());

        return $this->stmtsProvider;
    }
}
