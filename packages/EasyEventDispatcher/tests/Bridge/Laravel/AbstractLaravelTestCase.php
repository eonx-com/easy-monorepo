<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Bridge\Laravel;

use EonX\EasyEventDispatcher\Bridge\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyEventDispatcher\Tests\AbstractTestCase;
use EonX\EasyEventDispatcher\Tests\Bridge\Laravel\Stubs\LaravelEventDispatcherStub;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractTestCase
{
    private ?Application $app = null;

    protected function getApp(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = new Application(__DIR__);
        $this->app->register(EasyEventDispatcherServiceProvider::class);
        $this->app->instance(IlluminateDispatcherContract::class, new LaravelEventDispatcherStub());

        return $this->app;
    }
}
