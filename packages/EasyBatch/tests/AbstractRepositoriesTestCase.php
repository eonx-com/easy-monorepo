<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EonX\EasyBatch\Bridge\Doctrine\DbalStatementsProvider;

abstract class AbstractRepositoriesTestCase extends AbstractTestCase
{
    protected ?Connection $doctrineDbal = null;

    private ?DbalStatementsProvider $statementsProvider = null;

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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getDoctrineDbalConnection(): Connection
    {
        if ($this->doctrineDbal !== null) {
            return $this->doctrineDbal;
        }

        $this->doctrineDbal = DriverManager::getConnection([
            'url' => 'sqlite:///:memory:',
        ]);

        return $this->doctrineDbal;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getStatementsProvider(): DbalStatementsProvider
    {
        if ($this->statementsProvider !== null) {
            return $this->statementsProvider;
        }

        $this->statementsProvider = new DbalStatementsProvider($this->getDoctrineDbalConnection());

        return $this->statementsProvider;
    }
}
