<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolver;
use EonX\EasyLogging\Resolver\BugsnagSeverityResolverInterface;
use Monolog\Formatter\LineFormatter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(BugsnagSeverityResolverInterface::class, BugsnagSeverityResolver::class);

    $services->set(ConfigServiceId::BugsnagMonologHandlerFormatter->value, LineFormatter::class)
        ->arg('$ignoreEmptyContextAndExtra', true);

    $services->set(BugsnagMonologHandler::class)
        ->arg('$bugsnagSeverityResolver', service(BugsnagSeverityResolverInterface::class))
        ->arg('$level', param(ConfigParam::BugsnagHandlerLevel->value))
        ->call('setFormatter', [service(ConfigServiceId::BugsnagMonologHandlerFormatter->value)]);
};
