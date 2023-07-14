<?php

declare(strict_types=1);

namespace EonX\EasyLock\Tests\Bridge\Laravel;

use Doctrine\DBAL\DriverManager;
use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Bridge\Laravel\EasyLockServiceProvider;
use EonX\EasyLock\Tests\AbstractTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
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
            BridgeConstantsInterface::SERVICE_CONNECTION,
            DriverManager::getConnection([
                'url' => 'sqlite:///:memory:',
            ])
        );

        return $this->app;
    }
}
