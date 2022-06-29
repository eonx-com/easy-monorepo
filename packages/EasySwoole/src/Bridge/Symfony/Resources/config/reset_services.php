<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bridge\Symfony\AppStateResetters\SymfonyServicesAppStateResetter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(SymfonyServicesAppStateResetter::class)
        ->arg('$servicesResetter', service('services_resetter'));
};
