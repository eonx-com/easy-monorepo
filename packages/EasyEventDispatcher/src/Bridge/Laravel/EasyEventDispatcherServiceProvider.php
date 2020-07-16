<?php

declare(strict_types=1);

namespace EonX\EasyEventDispatcher\Bridge\Laravel;

use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcherContract;
use Illuminate\Support\ServiceProvider;

final class EasyEventDispatcherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventDispatcherInterface::class, function (): EventDispatcherInterface {
            return new EventDispatcher($this->app->make(IlluminateDispatcherContract::class));
        });
    }
}
