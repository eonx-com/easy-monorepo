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
        $app->instance(
            BridgeConstantsInterface::SERVICE_CONNECTION,
            DriverManager::getConnection(['url' => 'sqlite:///:memory:'])
        );

        return $this->app = $app;
    }
}
