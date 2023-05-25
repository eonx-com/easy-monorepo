<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ArrayIterator;
use EonX\EasySwoole\Bridge\Symfony\AppStateResetters\SymfonyServicesAppStateResetter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services->set(SymfonyServicesAppStateResetter::class)
        ->arg('$resettableServices', new ArrayIterator())
        ->arg('$resetMethods', []);
};
