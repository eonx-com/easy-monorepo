<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Tests\Bridge\Symfony\Stubs;

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
            'url' => 'sqlite:///:memory:',
        ]);
    }
}
