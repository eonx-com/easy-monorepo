<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Laravel;

use EonX\EasyLogging\Laravel\EasyLoggingServiceProvider;
use EonX\EasyLogging\Tests\Unit\AbstractUnitTestCase;
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

        if ($config !== null) {
            \config($config);
        }

        $this->app->register(EasyLoggingServiceProvider::class);
        $this->app->boot();

        return $this->app;
    }
}
