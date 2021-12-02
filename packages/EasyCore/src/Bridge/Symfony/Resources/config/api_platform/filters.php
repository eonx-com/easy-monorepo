<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Filter\VirtualSearchFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('eonx.api_platform.doctrine.orm.virtual_search_filter', VirtualSearchFilter::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->args([
            service('doctrine'),
            null,
            service('api_platform.iri_converter'),
            service('api_platform.property_accessor'),
            service('logger')
                ->ignoreOnInvalid(),
            '$identifiersExtractor' => service('api_platform.identifiers_extractor.cached')
                ->ignoreOnInvalid(),
            '$nameConverter' => service('api_platform.name_converter')
                ->ignoreOnInvalid(),
        ])
        ->abstract(true);

    $services->alias(VirtualSearchFilter::class, 'eonx.api_platform.doctrine.orm.virtual_search_filter');
};
