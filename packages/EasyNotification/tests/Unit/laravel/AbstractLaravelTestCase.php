<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Laravel;

use EonX\EasyNotification\Laravel\EasyNotificationServiceProvider;
use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\Tests\Stub\Provider\ConfigProviderStub;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
{
    private ?Application $app = null;

    protected function getApp(?array $config = null): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyNotificationServiceProvider::class);
        $this->app->boot();

        if ($config !== null) {
            $this->app->instance(ConfigProviderInterface::class, new ConfigProviderStub($config));
        }

        return $this->app;
    }
}
