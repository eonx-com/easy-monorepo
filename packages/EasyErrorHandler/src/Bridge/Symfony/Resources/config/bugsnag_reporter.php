<?php

declare(strict_types=1);

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\BugsnagReporterProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BugsnagReporterProvider::class)
        ->arg('$threshold', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD . '%');
};
