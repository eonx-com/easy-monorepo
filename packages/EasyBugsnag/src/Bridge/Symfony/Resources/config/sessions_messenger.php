<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bridge\Symfony\Session\SessionTrackingMessengerSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(SessionTrackingMessengerSubscriber::class);
};
