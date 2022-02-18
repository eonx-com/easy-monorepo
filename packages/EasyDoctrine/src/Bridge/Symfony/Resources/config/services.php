<?php

declare(strict_types=1);

use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;
use EonX\EasyDoctrine\Utils\ObjectCopier;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ObjectCopierInterface::class, ObjectCopier::class);
    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
};
