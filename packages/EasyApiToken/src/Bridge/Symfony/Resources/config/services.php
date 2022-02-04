<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Factories\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ApiTokenDecoderFactoryInterface::class, ApiTokenDecoderFactory::class)
        ->arg('$decoderProviders', tagged_iterator(BridgeConstantsInterface::TAG_DECODER_PROVIDER));

    $services
        ->set(ApiTokenDecoderInterface::class)
        ->factory([ref(ApiTokenDecoderFactoryInterface::class), 'buildDefault']);
};
