<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bundle\Enum\BundleParam;
use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLock\Doctrine\Listener\EasyLockDoctrineSchemaListener;
use Symfony\Component\Lock\LockFactory;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(LockFactory::class)
        ->arg('$store', service(ConfigServiceId::Store->value))
        ->call('setLogger', [service('logger')->ignoreOnInvalid()])
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services
        ->set(LockerInterface::class, Locker::class)
        ->arg('$store', service(ConfigServiceId::Store->value))
        ->arg('$lockFactory', service(LockFactory::class))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services
        ->set(EasyLockDoctrineSchemaListener::class)
        ->arg('$persistingStore', service(ConfigServiceId::Store->value));
};
