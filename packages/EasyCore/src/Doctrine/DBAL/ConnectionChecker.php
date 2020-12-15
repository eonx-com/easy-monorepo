<?php
declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\DBAL;

use Doctrine\DBAL\Connection;

final class ConnectionChecker
{
    public static function checkConnection(Connection $connection): void
    {
        $connection->executeStatement($connection->getDatabasePlatform()->getDummySelectSQL());
    }
}
