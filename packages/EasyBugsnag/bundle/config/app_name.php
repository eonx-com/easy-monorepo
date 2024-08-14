<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Common\Configurator\AppNameClientConfigurator;
use EonX\EasyBugsnag\Common\Resolver\AppNameResolverInterface;
use EonX\EasyBugsnag\Common\Resolver\DefaultAppNameResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AppNameResolverInterface::class, DefaultAppNameResolver::class)
        ->arg('$appNameEnvVar', param(ConfigParam::AppNameEnvVar->value));

    $services->set(AppNameClientConfigurator::class);
};
