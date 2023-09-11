<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Configurators\AppNameConfigurator;
use EonX\EasyBugsnag\Interfaces\AppNameResolverInterface;
use EonX\EasyBugsnag\Resolvers\DefaultAppNameResolver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(AppNameResolverInterface::class, DefaultAppNameResolver::class)
        ->arg('$appNameEnvVar', '%' . BridgeConstantsInterface::PARAM_APP_NAME_ENV_VAR . '%');

    $services->set(AppNameConfigurator::class);
};
