<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\ObjectCopierFactoryInterface;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;
use EonX\EasyDoctrine\Utils\ObjectCopierFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ObjectCopierFactoryInterface::class, ObjectCopierFactory::class);

    $services
        ->set(ObjectCopierInterface::class)
        ->factory([service(ObjectCopierFactoryInterface::class), 'create']);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
};
