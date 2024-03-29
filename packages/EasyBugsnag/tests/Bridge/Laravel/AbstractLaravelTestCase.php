<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Laravel;

use EonX\EasyBugsnag\Bridge\Laravel\EasyBugsnagServiceProvider;
use EonX\EasyBugsnag\Tests\AbstractTestCase;
use EonX\EasyUtils\Bridge\Laravel\EasyUtilsServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
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

        $this->app->register(EasyUtilsServiceProvider::class);
        $this->app->register(EasyBugsnagServiceProvider::class);
        $this->app->boot();

        return $this->app;
    }
}
