<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Stub\ConnectionFactory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

final class DoctrineDbalConnectionFactoryStub
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public static function create(): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    }
}
