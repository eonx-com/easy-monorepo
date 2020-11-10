<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Configurators\EventWebhookConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EventWebhookConfigurator::class)
        ->arg('$eventHeader', '%' . BridgeConstantsInterface::PARAM_EVENT_HEADER . '%')
        ->arg('$priority', BridgeConstantsInterface::DEFAULT_CONFIGURATOR_PRIORITY);
};
