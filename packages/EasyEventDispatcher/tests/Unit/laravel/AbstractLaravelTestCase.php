<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Tests\Unit\Laravel;

use EonX\EasyEventDispatcher\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyEventDispatcher\Tests\Stub\Dispatcher\LaravelEventDispatcherStub;
use EonX\EasyEventDispatcher\Tests\Unit\AbstractUnitTestCase;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;
use Laravel\Lumen\Application;

abstract class AbstractLaravelTestCase extends AbstractUnitTestCase
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
