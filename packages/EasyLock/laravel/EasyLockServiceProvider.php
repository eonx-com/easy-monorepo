<?php
declare(strict_types=1);

namespace EonX\EasyLock\Laravel;

use EonX\EasyLock\Bundle\Enum\BundleParam;
use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstants;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\PersistingStoreInterface;
use Symfony\Component\Lock\Store\StoreFactory;

final class EasyLockServiceProvider extends ServiceProvider
{
    private const DEFAULT_CONNECTION_ID = 'flock';

    public function register(): void
    {
        $this->app->singleton(
            ConfigServiceId::Store->value,
            static function (Container $app): PersistingStoreInterface {
                // If connection from config doesn't exist in container, use flock by default
                $conn = $app->has(ConfigServiceId::Connection->value)
                    ? $app->make(ConfigServiceId::Connection->value)
                    : self::DEFAULT_CONNECTION_ID;

                return StoreFactory::createStore($conn);
            }
        );

        $this->app->singleton(
            LockerInterface::class,
            static function (Container $app): LockerInterface {
                $loggerParams = \interface_exists(EasyLoggingBridgeConstants::class)
                    ? [EasyLoggingBridgeConstants::KEY_CHANNEL => BundleParam::LogChannel]
                    : [];

                return new Locker(
                    $app->make(ConfigServiceId::Store->value),
                    $app->make(LoggerInterface::class, $loggerParams)
                );
            }
        );
    }
}
