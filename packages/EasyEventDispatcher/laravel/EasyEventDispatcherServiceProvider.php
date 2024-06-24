<?php
declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Laravel;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyEventDispatcher\Laravel\Dispatcher\EventDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;
use Illuminate\Support\ServiceProvider;

final class EasyEventDispatcherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            EventDispatcherInterface::class,
            static fn (Container $app): EventDispatcherInterface => new EventDispatcher(
                $app->make(IlluminateDispatcherContract::class)
            )
        );
    }
}
