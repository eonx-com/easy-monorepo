<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use EonX\EasyErrorHandler\Common\Provider\DefaultErrorResponseBuilderProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultErrorResponseBuilderProvider::class)
        ->arg('$keys', param(ConfigParam::ResponseKeys->value))
        ->arg('$exceptionMessages', param(ConfigParam::ExceptionMessages->value))
        ->arg('$exceptionToStatusCode', param(ConfigParam::ExceptionToStatusCode->value));
};
