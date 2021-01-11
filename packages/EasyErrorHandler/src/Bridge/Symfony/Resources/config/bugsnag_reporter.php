<?php

declare(strict_types=1);

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\BugsnagReporterProvider;
use EonX\EasyErrorHandler\Bridge\Bugsnag\ErrorDetailsClientConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BugsnagReporterProvider::class)
        ->arg('$threshold', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD . '%')
        ->arg('$ignoredExceptions', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS . '%');

    $services->set(ErrorDetailsClientConfigurator::class);
};
