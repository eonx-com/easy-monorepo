<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Reporters\DefaultReporterProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(DefaultReporterProvider::class)
        ->arg('$ignoredExceptions', '%' . BridgeConstantsInterface::PARAM_LOGGER_IGNORED_EXCEPTIONS . '%');
};
