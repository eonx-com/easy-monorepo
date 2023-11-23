<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Handlers\BugsnagMonologHandler;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\DefaultBugsnagSeverityResolver;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\DefaultBugsnagSeverityResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(DefaultBugsnagSeverityResolverInterface::class, DefaultBugsnagSeverityResolver::class);

    $services->set(BugsnagMonologHandler::class)
        ->arg('$bugsnagSeverityResolver', service(DefaultBugsnagSeverityResolverInterface::class))
        ->arg('$level', '%' . BridgeConstantsInterface::PARAM_BUGSNAG_HANDLER_LEVEL . '%');
};
