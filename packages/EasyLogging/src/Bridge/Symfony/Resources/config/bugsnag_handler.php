<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Handlers\BugsnagMonologHandler;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\BugsnagSeverityResolver;
use EonX\EasyLogging\Bridge\Symfony\Monolog\Resolvers\BugsnagSeverityResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BugsnagSeverityResolverInterface::class, BugsnagSeverityResolver::class);

    $services->set(BugsnagMonologHandler::class)
        ->arg('$bugsnagSeverityResolver', service(BugsnagSeverityResolverInterface::class))
        ->arg('$level', param(BridgeConstantsInterface::PARAM_BUGSNAG_HANDLER_LEVEL));
};
