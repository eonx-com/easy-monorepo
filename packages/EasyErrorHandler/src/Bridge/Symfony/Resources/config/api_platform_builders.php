<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Provider\ApiPlatformErrorResponseBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$keys', param(BridgeConstantsInterface::PARAM_RESPONSE_KEYS))
        ->arg('$nameConverter', service('serializer.name_converter.metadata_aware'))
        ->arg('$transformValidationErrors', param(BridgeConstantsInterface::PARAM_TRANSFORM_VALIDATION_ERRORS));
};
