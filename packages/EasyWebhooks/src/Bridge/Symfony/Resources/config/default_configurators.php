<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhooks\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhooks\Configurators\BodyFormatterWebhookConfigurator;
use EonX\EasyWebhooks\Configurators\MethodWebhookConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(BodyFormatterWebhookConfigurator::class)
        ->arg('$priority', BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY);

    $services
        ->set(MethodWebhookConfigurator::class)
        ->arg('$method', '%' . BridgeConstantsInterface::PARAM_METHOD . '%')
        ->arg('$priority', BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY);
};
