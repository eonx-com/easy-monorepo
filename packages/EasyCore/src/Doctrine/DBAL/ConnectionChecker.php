<?php

declare(strict_types=1);

namespace EonX\EasyCore\Doctrine\DBAL;

use Doctrine\DBAL\Connection;

/**
 * @deprecated since 3.0.19, will be removed in 4.0. Use EonX\EasyAsync\Doctrine\ManagersSanityChecker instead.
 */
final class ConnectionChecker
{
    public static function checkConnection(Connection $connection): void
    {
        $connection->executeStatement($connection->getDatabasePlatform()->getDummySelectSQL());
    }
}
