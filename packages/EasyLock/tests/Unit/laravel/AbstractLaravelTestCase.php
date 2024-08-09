<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Laravel;

use Doctrine\DBAL\DriverManager;
use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Laravel\EasyLockServiceProvider;
use EonX\EasyLock\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApp(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyLockServiceProvider::class);
        $this->app->instance(
            ConfigServiceId::Connection->value,
            DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ])
        );

        return $this->app;
    }
}
