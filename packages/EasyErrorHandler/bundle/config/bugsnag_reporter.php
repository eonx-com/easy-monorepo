<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bugsnag\Configurator\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Configurator\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Ignorer\DefaultBugsnagExceptionIgnorer;
use EonX\EasyErrorHandler\Bugsnag\Provider\BugsnagErrorReporterProvider;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigParam;
use EonX\EasyErrorHandler\Bundle\Enum\ConfigTag;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BugsnagErrorReporterProvider::class)
        ->arg('$threshold', param(ConfigParam::BugsnagThreshold->value))
        ->arg('$exceptionIgnorers', tagged_iterator(ConfigTag::BugsnagExceptionIgnorer->value));

    $services->set(DefaultBugsnagExceptionIgnorer::class)
        ->arg('$ignoredExceptions', param(ConfigParam::BugsnagIgnoredExceptions->value));

    $services->set(ErrorDetailsClientConfigurator::class);

    $services->set(SeverityClientConfigurator::class);

    $services->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', param(ConfigParam::BugsnagHandledExceptions->value));
};
