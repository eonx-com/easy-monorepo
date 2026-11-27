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
        $failure = null;

        // Each connection is created separately: getConnections() creates them all and stops at the first one that
        // cannot be created, e.g. a missing environment variable, so the open ones would not be closed
        foreach (\array_keys($this->managerRegistry->getConnectionNames()) as $name) {
            try {
                $connection = $this->managerRegistry->getConnection($name);
            } catch (Throwable $throwable) {
                $failure ??= $throwable;

                continue;
            }

            if ($connection instanceof Connection) {
                $connections[] = $connection;
            }
        }

        foreach ($connections as $connection) {
            try {
                $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL())
                    ->free();
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        // Every connection is closed, even when closing another one fails, e.g. a logging middleware that throws
        foreach ($connections as $connection) {
            try {
                $connection->close();
            } catch (Throwable $throwable) {
                $failure ??= $throwable;
            }
        }

        // Thrown once all the connections are closed, so the subscriber logs it
        if ($failure !== null) {
            throw $failure;
        }
    }
}
