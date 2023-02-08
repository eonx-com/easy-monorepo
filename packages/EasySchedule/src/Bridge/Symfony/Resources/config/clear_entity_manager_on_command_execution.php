<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySchedule\Bridge\Doctrine\CommandExecutedEventListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(CommandExecutedEventListener::class)
        ->tag('kernel.event_listener');
};
