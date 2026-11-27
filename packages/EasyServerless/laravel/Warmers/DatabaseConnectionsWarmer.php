<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel\Warmers;

use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use Illuminate\Contracts\Container\Container;

/**
 * Closes the database connections opened during the initialization: every restored execution environment would share
 * them. It must run last.
 */
final readonly class DatabaseConnectionsWarmer implements SnapStartWarmerInterface
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function warm(): void
    {
        // No connection is open when nothing created the database manager
        if ($this->container->resolved('db') === false) {
            return;
        }

        /** @var \Illuminate\Database\DatabaseManager $databaseManager */
        $databaseManager = $this->container->make('db');

        foreach (\array_keys($databaseManager->getConnections()) as $name) {
            $databaseManager->disconnect($name);
        }
    }
}
