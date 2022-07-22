<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Symfony\Builder\ApiPlatformBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiPlatformBuilderProvider::class)
        ->arg('$keys', '%' . BridgeConstantsInterface::PARAM_RESPONSE_KEYS . '%')
        ->arg('$transformValidationErrors', '%' . BridgeConstantsInterface::PARAM_TRANSFORM_VALIDATION_ERRORS . '%');
};
