<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EonX\EasyWebhook\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Webhook;

abstract class AbstractStoreTestCase extends AbstractTestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineDbal;

    /**
     * @var \EonX\EasyWebhook\Bridge\Doctrine\DbalStatementsProvider
     */
    private $stmtsProvider;

    protected function createWebhookForSendAfter(
        ?\DateTimeInterface $sendAfter = null,
        ?string $status = null
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
        if ($this->doctrineDbal !== null) {
            return $this->doctrineDbal;
        }

        return $this->doctrineDbal = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);
    }

    protected function setUp(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->connect();

        foreach ($this->getStmtsProvider()->migrateStatements() as $statement) {
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

    private function getStmtsProvider(): DbalStatementsProvider
    {
        if ($this->stmtsProvider !== null) {
            return $this->stmtsProvider;
        }

        return $this->stmtsProvider = new DbalStatementsProvider($this->getDoctrineDbalConnection());
    }
}
