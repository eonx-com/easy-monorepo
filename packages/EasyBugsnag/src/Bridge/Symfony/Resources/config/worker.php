<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Bridge\Symfony\Worker\WorkerMessageReceivedListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(WorkerMessageReceivedListener::class)
        ->tag('kernel.event_listener');
};
