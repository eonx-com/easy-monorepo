<?php

declare(strict_types=1);

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Config\StreamHandlerConfigProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StreamHandlerConfigProvider::class)
        ->arg('$level', '%' . BridgeConstantsInterface::PARAM_STREAM_HANDLER_LEVEL . '%');
};
