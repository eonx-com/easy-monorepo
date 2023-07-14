<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use EonX\EasyAsync\Bridge\Laravel\Providers\EasyAsyncServiceProvider;
use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function createApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyEventDispatcherServiceProvider::class);
        $this->app->register(EasyAsyncServiceProvider::class);
        $this->app->register(EasyRandomServiceProvider::class);

        return $this->app;
    }
}
