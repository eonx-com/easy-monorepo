<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bridge\BridgeConstantsInterface;
use EonX\EasyApiPlatform\Filters\AdvancedSearchFilter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('eonx.api_platform.doctrine.orm.advanced_search_filter', AdvancedSearchFilter::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->arg('$managerRegistry', service('doctrine'))
        ->arg('$iriConverter', service('api_platform.iri_converter'))
        ->arg('$propertyAccessor', service('api_platform.property_accessor'))
        ->arg('$logger', service('logger')->ignoreOnInvalid())
        ->arg('$nameConverter', service('api_platform.name_converter')->ignoreOnInvalid())
        ->arg('$iriFields', param(BridgeConstantsInterface::PARAM_ADVANCED_SEARCH_FILTER_IRI_FIELDS))
        ->private()
        ->abstract();

    $services->alias(AdvancedSearchFilter::class, 'eonx.api_platform.doctrine.orm.advanced_search_filter');
};
