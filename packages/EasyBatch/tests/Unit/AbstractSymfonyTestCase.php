<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Unit;

use Doctrine\DBAL\Connection;
use EonX\EasyBatch\Doctrine\Provider\DoctrineDbalStatementProvider;
use EonX\EasyBatch\Tests\Stub\Kernel\KernelStub;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends AbstractUnitTestCase
{
    private ?KernelInterface $kernel = null;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function setUp(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get(Connection::class);

        foreach ((new DoctrineDbalStatementProvider($connection))->migrateStatements() as $sql) {
            $connection->executeStatement($sql);
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

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get(Connection::class);

        foreach ((new DoctrineDbalStatementProvider($connection))->rollbackStatements() as $sql) {
            $connection->executeStatement($sql);
        }

        parent::tearDown();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->getKernel()
            ->getContainer();
    }

    protected function getKernel(): KernelInterface
    {
        if ($this->kernel !== null) {
            return $this->kernel;
        }

        $_SERVER['APP_SECRET'] = 'my-secret';

        $this->kernel = new KernelStub('test', true);
        $this->kernel->boot();

        return $this->kernel;
    }
}
