<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bugsnag\Configurator\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Provider\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Bugsnag\Resolver\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bugsnag\Resolver\DefaultBugsnagIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BugsnagErrorReporterProvider::class)
        ->arg('$threshold', param(ConfigParam::BugsnagThreshold->value));

    $services->set(BugsnagIgnoreExceptionsResolverInterface::class, DefaultBugsnagIgnoreExceptionsResolver::class)
        ->arg('$ignoredExceptions', param(ConfigParam::BugsnagIgnoredExceptions->value))
        ->arg('$ignoreValidationErrors', param(ConfigParam::BugsnagIgnoreValidationErrors->value));

    $services->set(ErrorDetailsClientConfigurator::class);

    $services->set(SeverityClientConfigurator::class);

    $services->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', param(ConfigParam::BugsnagHandledExceptions->value));
};
