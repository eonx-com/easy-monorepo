<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Laravel;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class EasyLockServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    private const DEFAULT_CONNECTION_ID = 'flock';

    public function register(): void
    {
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_STORE,
            static function (Container $app): PersistingStoreInterface {
                // If connection from config doesn't exist in container, use flock by default.
                $conn = $app->has(BridgeConstantsInterface::SERVICE_CONNECTION)
                    ? $app->make(BridgeConstantsInterface::SERVICE_CONNECTION)
                    : self::DEFAULT_CONNECTION_ID;

                return StoreFactory::createStore($conn);
            }
        );

        $this->app->singleton(
            LockServiceInterface::class,
            static function (Container $app): LockServiceInterface {
                return new LockService(
                    $app->make(BridgeConstantsInterface::SERVICE_STORE),
                    $app->make(LoggerInterface::class)
                );
            }
        );
    }
}
