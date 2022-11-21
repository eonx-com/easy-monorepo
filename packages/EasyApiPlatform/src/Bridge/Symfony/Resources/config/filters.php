<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Filters\AdvancedSearchFilter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('eonx.api_platform.doctrine.orm.advanced_search_filter', AdvancedSearchFilter::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->args([
            service('doctrine'),
            service('api_platform.iri_converter'),
            service('api_platform.property_accessor'),
            service('logger')
                ->ignoreOnInvalid(),
            '$nameConverter' => service('api_platform.name_converter')
                ->ignoreOnInvalid(),
        ])
        ->private()
        ->abstract();

    $services->alias(AdvancedSearchFilter::class, 'eonx.api_platform.doctrine.orm.advanced_search_filter');
};
