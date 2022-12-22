<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony;

use EonX\EasyBatch\Bridge\Doctrine\DbalStatementsProvider;
use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractSymfonyTestCase extends KernelTestCase
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function setUp(): void
    {
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = self::getContainer()->get('doctrine.dbal.default_connection');

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
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = self::getContainer()->get('doctrine.dbal.default_connection');

        foreach ((new DbalStatementsProvider($conn))->rollbackStatements() as $sql) {
            $conn->executeStatement($sql);
        }

        parent::tearDown();
    }

    /**
     * @param mixed[] $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        $_SERVER['APP_SECRET'] = 'my-secret';

        return new ApplicationKernel('test', false);
    }
}
