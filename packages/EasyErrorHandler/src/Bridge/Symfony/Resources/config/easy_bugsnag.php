<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyErrorHandler\Bridge\BridgeConstantsInterface;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Configurators\ErrorDetailsClientConfigurator;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Configurators\SeverityClientConfigurator;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Configurators\UnhandledClientConfigurator;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Ignorers\DefaultBugsnagExceptionIgnorer;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Providers\BugsnagErrorReporterProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultBugsnagExceptionIgnorer::class)
        ->arg('$ignoredExceptions', param(BridgeConstantsInterface::PARAM_BUGSNAG_IGNORED_EXCEPTIONS));

    $containerBuilder->registerForAutoconfiguration(BugsnagExceptionIgnorerInterface::class)
        ->addTag(BridgeConstantsInterface::TAG_BUGSNAG_EXCEPTION_IGNORER);

    $services->set(BugsnagErrorReporterProvider::class)
        ->arg('$exceptionIgnorers', tagged_iterator(BridgeConstantsInterface::TAG_BUGSNAG_EXCEPTION_IGNORER))
        ->arg('$threshold', param(BridgeConstantsInterface::PARAM_BUGSNAG_THRESHOLD));

    $services->set(ErrorDetailsClientConfigurator::class);

    $services->set(SeverityClientConfigurator::class);

    $services->set(UnhandledClientConfigurator::class)
        ->arg('$handledExceptionClasses', param(BridgeConstantsInterface::PARAM_BUGSNAG_HANDLED_EXCEPTIONS));
};
