<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriver;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(HashedApiKeyDriverInterface::class, HashedApiKeyDriver::class);

    $services
        ->set(ApiTokenDecoderFactoryInterface::class, ApiTokenDecoderFactory::class)
        ->arg('$decoderProviders', tagged_iterator(ConfigTag::DecoderProvider->value))
        ->tag('kernel.reset', ['method' => 'reset']);

    $services
        ->set(DecoderInterface::class)
        ->factory([service(ApiTokenDecoderFactoryInterface::class), 'buildDefault']);
};
