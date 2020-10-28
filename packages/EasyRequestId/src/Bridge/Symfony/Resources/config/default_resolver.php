<?php

declare(strict_types=1);

use EonX\EasyRequestId\Bridge\BridgeConstantsInterface;
use EonX\EasyRequestId\DefaultResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(DefaultResolver::class)
        ->arg('$requestIdHeader', '%' . BridgeConstantsInterface::PARAM_DEFAULT_REQUEST_ID_HEADER . '%')
        ->arg('$correlationIdHeader', '%' . BridgeConstantsInterface::PARAM_DEFAULT_CORRELATION_ID_HEADER . '%');
};
