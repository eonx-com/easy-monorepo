<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

use EonX\EasyAsync\Bridge\Laravel\Providers\EasyAsyncServiceProvider;
use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyRandom\Bridge\Laravel\EasyRandomServiceProvider;
use Laravel\Lumen\Application;

abstract class AbstractLumenTestCase extends AbstractTestCase
{
    /**
     * @var \Laravel\Lumen\Application
     */
    private $app;

    protected function createApplication(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $app = new Application(__DIR__);
        $app->register(EasyEventDispatcherServiceProvider::class);
        $app->register(EasyAsyncServiceProvider::class);
        $app->register(EasyRandomServiceProvider::class);

        return $this->app = $app;
    }
}
