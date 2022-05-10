<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyBatch\Tests\AbstractTestCase;
use EonX\EasyBatch\Tests\Bridge\Symfony\Stubs\KernelStub;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractTestCase
{
    private ?KernelInterface $kernel = null;

    protected function getKernel(): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $_SERVER['APP_SECRET'] = 'my-secret';

        $kernel = new KernelStub('test', true);
        $kernel->boot();

        return $this->kernel = $kernel;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function setUp(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $container->get(Connection::class);

        foreach ((new DbalStatementsProvider($conn))->migrateStatements() as $sql) {
            $conn->executeStatement($sql);
        }

        parent::setUp();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function tearDown(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $container->get(Connection::class);

        foreach ((new DbalStatementsProvider($conn))->rollbackStatements() as $sql) {
            $conn->executeStatement($sql);
        }

        parent::setUp();
    }
}
