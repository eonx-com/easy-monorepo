<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Bridge\Laravel;

use EonX\EasyNotification\Bridge\Laravel\EasyNotificationServiceProvider;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Tests\AbstractTestCase;
use EonX\EasyNotification\Tests\Stubs\ConfigFinderStub;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    /**
     * @param mixed[]|null $config
     */
    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyNotificationServiceProvider::class);
        $this->app->boot();

        if ($config !== null) {
            $this->app->instance(ConfigFinderInterface::class, new ConfigFinderStub($config));
        }

        return $this->app;
    }
}
