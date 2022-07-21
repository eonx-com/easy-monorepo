<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Providers\BugsnagReporterProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BugsnagReporterProvider::class)
        ->arg('$threshold', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD . '%')
        ->arg('$ignoredExceptions', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS . '%');

    $services
        ->set(ErrorDetailsClientConfigurator::class)
        ->set(SeverityClientConfigurator::class);

    $services
        ->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_HANDLED_EXCEPTIONS . '%');
};
