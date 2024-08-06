<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Bugsnag\Ignorer\ApiPlatformBugsnagExceptionIgnorer;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ApiPlatformBugsnagExceptionIgnorer::class);
};
