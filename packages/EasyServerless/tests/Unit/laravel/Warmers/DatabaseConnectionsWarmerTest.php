<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Laravel\Warmers;

use EonX\EasyServerless\Laravel\Warmers\DatabaseConnectionsWarmer;
use EonX\EasyServerless\Tests\Unit\Laravel\AbstractLumenTestCase;

final class DatabaseConnectionsWarmerTest extends AbstractLumenTestCase
{
    public function testWarmDisconnectsResolvedConnections(): void
    {
        $app = $this->getApplication([]);
        // Loads the Lumen database configuration first, so it does not override the connections below
        $app->configure('database');
        \config([
            'database.connections.first' => [
                'database' => ':memory:',
                'driver' => 'sqlite',
            ],
            'database.connections.second' => [
                'database' => ':memory:',
                'driver' => 'sqlite',
            ],
        ]);
        /** @var \Illuminate\Database\DatabaseManager $databaseManager */
        $databaseManager = $app->make('db');
        $firstConnection = $databaseManager->connection('first');
        $firstConnection->getPdo();
        $secondConnection = $databaseManager->connection('second');
        $secondConnection->getPdo();
        $sut = new DatabaseConnectionsWarmer($app);

        $sut->warm();

        self::assertNull($firstConnection->getRawPdo());
        self::assertNull($secondConnection->getRawPdo());
    }

    public function testWarmDoesNotResolveDatabaseManager(): void
    {
        $app = $this->getApplication([]);
        $sut = new DatabaseConnectionsWarmer($app);

        $sut->warm();

        self::assertFalse($app->resolved('db'));
    }
}
