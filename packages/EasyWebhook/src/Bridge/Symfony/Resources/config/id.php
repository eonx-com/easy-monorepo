<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Configurators\IdWebhookConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(IdWebhookConfigurator::class)
        ->arg('$idHeader', '%' . BridgeConstantsInterface::PARAM_ID_HEADER . '%')
        ->arg('$priority', BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY);
};
