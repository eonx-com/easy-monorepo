<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Laravel;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class EasyLockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BridgeConstantsInterface::SERVICE_STORE, function (): PersistingStoreInterface {
            return StoreFactory::createStore($this->app->make(BridgeConstantsInterface::SERVICE_CONNECTION));
        });

        $this->app->singleton(LockServiceInterface::class, function (): LockServiceInterface {
            return new LockService(
                $this->app->make(BridgeConstantsInterface::SERVICE_STORE),
                $this->app->make(LoggerInterface::class)
            );
        });
    }
}
