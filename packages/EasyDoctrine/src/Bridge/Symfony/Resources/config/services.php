<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
};
