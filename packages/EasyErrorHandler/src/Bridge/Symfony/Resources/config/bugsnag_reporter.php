<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Providers\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers\DefaultBugsnagIgnoreExceptionsResolver;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BugsnagErrorReporterProvider::class)
        ->arg('$threshold', param(BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD));

    $services->set(BugsnagIgnoreExceptionsResolverInterface::class, DefaultBugsnagIgnoreExceptionsResolver::class)
        ->arg('$ignoredExceptions', param(BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS))
        ->arg('$ignoreValidationErrors', param(BridgeConstantsInterface::PARAM_BUGSNAG_IGNORE_VALIDATION_ERRORS));

    $services->set(ErrorDetailsClientConfigurator::class);

    $services->set(SeverityClientConfigurator::class);

    $services->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', param(BridgeConstantsInterface::PARAM_BUGSNAG_HANDLED_EXCEPTIONS));
};
