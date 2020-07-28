<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Providers\FromConfigDecoderProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(FromConfigDecoderProvider::class)
        ->arg('$config', '%' . BridgeConstantsInterface::PARAM_DECODERS . '%')
        ->arg('$defaultFactories', '%' . BridgeConstantsInterface::PARAM_DEFAULT_FACTORIES . '%');
};
