<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Filter\VirtualSearchFilter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('eonx.api_platform.doctrine.orm.virtual_search_filter', VirtualSearchFilter::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->args([
            ref('doctrine'),
            null,
            ref('api_platform.iri_converter'),
            ref('api_platform.property_accessor'),
            ref('logger')
                ->ignoreOnInvalid(),
            '$identifiersExtractor' => ref('api_platform.identifiers_extractor.cached')
                ->ignoreOnInvalid(),
            '$nameConverter' => ref('api_platform.name_converter')
                ->ignoreOnInvalid(),
        ])
        ->abstract(true);

    $services->alias(VirtualSearchFilter::class, 'eonx.api_platform.doctrine.orm.virtual_search_filter');
};
