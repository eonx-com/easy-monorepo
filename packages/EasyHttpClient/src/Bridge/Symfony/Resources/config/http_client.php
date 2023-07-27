<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Implementations\Symfony\WithEventsHttpClient;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(BridgeConstantsInterface::SERVICE_HTTP_CLIENT, WithEventsHttpClient::class)
        ->arg('$modifiers', tagged_iterator(BridgeConstantsInterface::TAG_REQUEST_DATA_MODIFIER))
        ->arg('$modifiersEnabled', '%' . BridgeConstantsInterface::PARAM_MODIFIERS_ENABLED . '%')
        ->arg('$modifiersWhitelist', '%' . BridgeConstantsInterface::PARAM_MODIFIERS_WHITELIST . '%');
};
