<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;
use EonX\EasyCore\Bridge\Symfony\Messenger\StopWorkerOnEmClosedEventListener;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Messenger
    $services
        ->set(StopWorkerOnEmClosedEventListener::class)
        ->tag('kernel.event_listener');

    $services->set(ForBuildEnvVarProcessor::class);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
};
