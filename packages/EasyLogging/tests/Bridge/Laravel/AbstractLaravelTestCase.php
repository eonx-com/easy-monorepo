<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Bridge\Laravel;

use EonX\EasyLogging\Bridge\Laravel\EasyLoggingServiceProvider;
use EonX\EasyLogging\Tests\AbstractTestCase;
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
        $app->register(EasyLoggingServiceProvider::class);
        $app->boot();

        if ($config !== null) {
            \config($config);
        }

        return $this->app = $app;
    }
}
