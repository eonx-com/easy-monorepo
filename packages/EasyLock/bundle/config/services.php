<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bundle\Enum\BundleParam;
use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLock\Doctrine\Listener\EasyLockDoctrineSchemaListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(LockerInterface::class, Locker::class)
        ->arg('$store', service(ConfigServiceId::Store->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services
        ->set(EasyLockDoctrineSchemaListener::class)
        ->arg('$persistingStore', service(ConfigServiceId::Store->value));
};
