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
    protected function getStatementsProvider(): DoctrineDbalStatementProvider
    {
        if ($this->statementsProvider !== null) {
            return $this->statementsProvider;
        }

        $this->statementsProvider = new DoctrineDbalStatementProvider($this->getDoctrineDbalConnection());

        return $this->statementsProvider;
    }
}
