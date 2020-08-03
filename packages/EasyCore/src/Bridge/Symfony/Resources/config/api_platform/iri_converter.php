<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing\IriConverter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(IriConverter::class)
        ->decorate('api_platform.iri_converter')
        ->args([ref('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Routing\IriConverter.inner')]);
};
