<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Doctrine\Warmer;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use Throwable;

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
        $connections = [];
        $creationThrowable = null;

        // Each connection is created separately: getConnections() creates them all and stops at the first one that
        // cannot be created, e.g. a missing environment variable, so the open ones would not be closed
        foreach (\array_keys($this->managerRegistry->getConnectionNames()) as $name) {
            try {
                $connection = $this->managerRegistry->getConnection($name);
            } catch (Throwable $throwable) {
                $creationThrowable ??= $throwable;

                continue;
            }

            if ($connection instanceof Connection) {
                $connections[] = $connection;
            }
        }

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

        // Thrown once the connections are closed, so the subscriber logs it
        if ($creationThrowable !== null) {
            throw $creationThrowable;
        }
    }
}
