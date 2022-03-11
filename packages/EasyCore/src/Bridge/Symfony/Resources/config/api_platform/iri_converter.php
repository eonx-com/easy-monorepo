<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing\IriConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IriConverter::class)
        ->decorate('api_platform.iri_converter')
        ->args([service('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing\IriConverter.inner')]);
};
