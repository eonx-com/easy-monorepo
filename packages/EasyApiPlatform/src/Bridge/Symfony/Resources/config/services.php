<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Routing\IriConverter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(IriConverter::class)
        ->decorate('api_platform.iri_converter')
        ->arg('$decorated', service('.inner'));
};
