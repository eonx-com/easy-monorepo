<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Doctrine\Listener\EasyLockDoctrineSchemaListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EasyLockDoctrineSchemaListener::class)
        ->arg('$persistingStore', service(ConfigServiceId::Store->value));
};
