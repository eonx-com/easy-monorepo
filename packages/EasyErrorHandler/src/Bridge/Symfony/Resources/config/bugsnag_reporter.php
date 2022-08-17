<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Providers\BugsnagReporterProvider;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers\DefaultBugsnagIgnoreExceptionsResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BugsnagReporterProvider::class)
        ->arg('$threshold', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD . '%');

    $services
        ->set(BugsnagIgnoreExceptionsResolverInterface::class, DefaultBugsnagIgnoreExceptionsResolver::class)
        ->arg('$ignoredExceptions', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS . '%')
        ->arg('$ignoreValidationErrors', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_IGNORE_VALIDATION_ERRORS . '%');

    $services
        ->set(ErrorDetailsClientConfigurator::class)
        ->set(SeverityClientConfigurator::class);

    $services
        ->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_HANDLED_EXCEPTIONS . '%');
};
