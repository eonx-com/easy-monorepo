<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;
use Illuminate\Support\ServiceProvider;

final class EasyEventDispatcherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            EventDispatcherInterface::class,
            static function (Container $app): EventDispatcherInterface {
                return new EventDispatcher($app->make(IlluminateDispatcherContract::class));
            },
        );
    }
}
