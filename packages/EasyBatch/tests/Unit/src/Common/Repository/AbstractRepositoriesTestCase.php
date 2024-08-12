<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit\Common\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use EonX\EasyBatch\Doctrine\Provider\DoctrineDbalStatementProvider;
use EonX\EasyBatch\Tests\Unit\AbstractUnitTestCase;

abstract class AbstractRepositoriesTestCase extends AbstractUnitTestCase
{
    protected ?Connection $doctrineDbal = null;

    private ?DoctrineDbalStatementProvider $statementsProvider = null;

    protected function setUp(): void
    {
        $connection = $this->getDoctrineDbalConnection();

        foreach ($this->getStatementsProvider()->migrateStatements() as $statement) {
            $connection->executeStatement($statement);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $connection = $this->getDoctrineDbalConnection();

        foreach ($this->getStatementsProvider()->rollbackStatements() as $statement) {
            $connection->executeStatement($statement);
        }

        $connection->close();

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
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        return $this->doctrineDbal;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getStatementsProvider(): DoctrineDbalStatementProvider
    {
        if ($this->statementsProvider !== null) {
            return $this->statementsProvider;
        }

        $this->statementsProvider = new DoctrineDbalStatementProvider($this->getDoctrineDbalConnection());

        return $this->statementsProvider;
    }
}
