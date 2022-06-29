<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Bridge\Laravel;

use EonX\EasyBugsnag\Bridge\Laravel\EasyBugsnagServiceProvider;
use EonX\EasyBugsnag\Tests\AbstractTestCase;
use EonX\EasyUtils\Bridge\Laravel\EasyUtilsServiceProvider;
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

        if ($config !== null) {
            \config($config);
        }

        $app->register(EasyUtilsServiceProvider::class);
        $app->register(EasyBugsnagServiceProvider::class);
        $app->boot();

        return $this->app = $app;
    }
}
