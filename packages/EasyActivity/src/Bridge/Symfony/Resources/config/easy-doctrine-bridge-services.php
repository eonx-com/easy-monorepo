<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EasyDoctrineEntityEventsSubscriber::class)
        ->tag('kernel.event_subscriber');
};
