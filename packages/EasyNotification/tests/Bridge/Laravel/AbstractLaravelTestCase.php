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
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    /**
     * @param null|mixed[] $config
     */
    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyNotificationServiceProvider::class);
        $app->boot();

        if ($config !== null) {
            $app->instance(ConfigFinderInterface::class, new ConfigFinderStub($config));
        }

        return $this->app = $app;
    }
}
