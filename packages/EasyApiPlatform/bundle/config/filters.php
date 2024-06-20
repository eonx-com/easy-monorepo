<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bundle\Enum\ConfigParam;
use EonX\EasyApiPlatform\Filter\AdvancedSearchFilter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('eonx.api_platform.doctrine.orm.advanced_search_filter', AdvancedSearchFilter::class)
        ->autoconfigure(false)
        ->autowire(false)
        ->arg('$managerRegistry', service('doctrine'))
        ->arg('$iriConverter', service('api_platform.iri_converter'))
        ->arg('$propertyAccessor', service('api_platform.property_accessor'))
        ->arg('$logger', service('logger')->ignoreOnInvalid())
        ->arg('$identifiersExtractor', service('api_platform.identifiers_extractor')->ignoreOnInvalid())
        ->arg('$nameConverter', service('api_platform.name_converter')->ignoreOnInvalid())
        ->arg('$iriFields', param(ConfigParam::AdvancedSearchFilterIriFields->value))
        ->private()
        ->abstract();

    $services->alias(AdvancedSearchFilter::class, 'eonx.api_platform.doctrine.orm.advanced_search_filter');
};
