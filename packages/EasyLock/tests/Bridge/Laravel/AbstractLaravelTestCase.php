<?php

declare(strict_types=1);

namespace EonX\EasyLock\Tests\Bridge\Laravel;

use Doctrine\DBAL\DriverManager;
use EonX\EasyEventDispatcher\Tests\AbstractTestCase;
use EonX\EasyLock\Bridge\Laravel\EasyLockServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function getApp(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyLockServiceProvider::class);
        $app->instance('in_memory_connection', DriverManager::getConnection(['url' => 'sqlite:///:memory:']));

        \config(['easy-lock.connection' => 'in_memory_connection']);

        return $this->app = $app;
    }
}
