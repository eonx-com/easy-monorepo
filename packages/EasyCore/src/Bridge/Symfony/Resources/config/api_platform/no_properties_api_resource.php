<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Metadata\NoPropertiesPropertyNameCollectionFactory')
        ->decorate('api_platform.metadata.property.name_collection_factory.property_info', null, -20)
        ->args(
            [ref('EonX\EasyCore\Bridge\Symfony\ApiPlatform\Metadata\NoPropertiesPropertyNameCollectionFactory.inner')]
        );
};
