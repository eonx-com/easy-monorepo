<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Laravel;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class EasyLockServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-lock.php' => \base_path('config/easy-lock.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-lock.php', 'easy-lock');

        $this->app->singleton(LockServiceInterface::class, function (): LockServiceInterface {
            return new LockService(
                StoreFactory::createStore($this->app->make(\config('easy-lock.connection'))),
                $this->app->make(LoggerInterface::class)
            );
        });
    }
}
