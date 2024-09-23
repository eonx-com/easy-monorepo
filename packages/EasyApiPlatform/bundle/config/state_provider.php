<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiPlatform\Common\StateProvider\ReadStateProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ReadStateProvider::class)
        ->decorate('api_platform.state_provider.read', priority: 1)
        ->arg('$decorated', service('.inner'));
};
