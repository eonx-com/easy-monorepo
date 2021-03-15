<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EonX\EasyAsync\Bridge\Doctrine\DbalStatementsProvider;

abstract class AbstractStoreTestCase extends AbstractTestCase
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $doctrineDbal;

    /**
     * @var \EonX\EasyAsync\Bridge\Doctrine\DbalStatementsProvider
     */
    private $statementsProvider;

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

    protected function getStatementsProvider(): DbalStatementsProvider
    {
        if ($this->statementsProvider !== null) {
            return $this->statementsProvider;
        }

        return $this->statementsProvider = new DbalStatementsProvider($this->getDoctrineDbalConnection());
    }

    protected function setUp(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $conn->connect();

        foreach ($this->getStatementsProvider()->migrateStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $conn = $this->getDoctrineDbalConnection();

        foreach ($this->getStatementsProvider()->rollbackStatements() as $statement) {
            $conn->executeStatement($statement);
        }

        $conn->close();

        parent::tearDown();
    }
}
