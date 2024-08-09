<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Laravel;

use EonX\EasyBugsnag\Laravel\EasyBugsnagServiceProvider;
use EonX\EasyUtils\Laravel\EasyUtilsServiceProvider;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

abstract class AbstractLaravelTestCase extends TestCase
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
