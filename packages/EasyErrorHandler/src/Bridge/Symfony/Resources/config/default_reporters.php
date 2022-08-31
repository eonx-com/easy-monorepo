<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Providers\DefaultErrorReporterProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultErrorReporterProvider::class)
        ->arg('$ignoredExceptions', param(BridgeConstantsInterface::PARAM_LOGGER_IGNORED_EXCEPTIONS));
};
