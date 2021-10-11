<?php

declare(strict_types=1);

use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriber;
use EonX\EasyDoctrine\Subscribers\EntityEventSubscriberInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
    $services->set(EntityEventSubscriberInterface::class, EntityEventSubscriber::class)
        ->public()
        ->arg('$entities', []);
};
