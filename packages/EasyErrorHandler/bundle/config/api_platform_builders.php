<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\ApiPlatform\Provider\ApiPlatformErrorResponseBuilderProvider;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformErrorResponseBuilderProvider::class)
        ->arg('$keys', param(ConfigParam::ResponseKeys->value))
        ->arg('$transformValidationErrors', param(ConfigParam::TransformValidationErrors->value));
};
