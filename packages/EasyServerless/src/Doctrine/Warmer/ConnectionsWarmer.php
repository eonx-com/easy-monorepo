<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Doctrine\Warmer;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;

/**
 * Runs a query on each connection, which loads the driver and the platform, then closes all the connections, including
 * the ones opened by the previous warmers: every restored execution environment would share them. It must run last.
 */
final readonly class ConnectionsWarmer implements SnapStartWarmerInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function warm(): void
    {
        $connections = \array_filter(
            $this->managerRegistry->getConnections(),
            static fn (object $connection): bool => $connection instanceof Connection
        );

        try {
            foreach ($connections as $connection) {
                $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL())
                    ->free();
            }
        } finally {
            foreach ($connections as $connection) {
                $connection->close();
            }
        }
    }
}
