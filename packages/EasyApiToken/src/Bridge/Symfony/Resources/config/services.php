<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Factories\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Tokens\HashedApiKeyDriver;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(HashedApiKeyDriverInterface::class, HashedApiKeyDriver::class);

    $services
        ->set(ApiTokenDecoderFactoryInterface::class, ApiTokenDecoderFactory::class)
        ->arg('$decoderProviders', tagged_iterator(BridgeConstantsInterface::TAG_DECODER_PROVIDER))
        ->tag('kernel.reset', ['method' => 'reset']);

    $services
        ->set(ApiTokenDecoderInterface::class)
        ->factory([service(ApiTokenDecoderFactoryInterface::class), 'buildDefault']);
};
