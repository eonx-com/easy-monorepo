<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyWebhook\Bridge\BridgeConstantsInterface;
use EonX\EasyWebhook\Middleware\BodyFormatterMiddleware;
use EonX\EasyWebhook\Middleware\MethodMiddleware;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Body formatter
    $services->set(BodyFormatterMiddleware::class);

    // Method
    $services
        ->set(MethodMiddleware::class)
        ->arg('$method', '%' . BridgeConstantsInterface::PARAM_METHOD . '%');
};
