<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;
use EonX\EasyCore\Bridge\Symfony\Messenger\StopWorkerOnEmClosedEventListener;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyCore\Doctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
