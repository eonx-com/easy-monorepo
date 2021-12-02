<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\PsrLogger\LogHttpRequestSentListener;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $listener = $services
        ->set(LogHttpRequestSentListener::class)
        ->tag('kernel.event_listener')
        ->tag('monolog.logger', ['channel' => BridgeConstantsInterface::LOG_CHANNEL]);

    if (\interface_exists(LoggerFactoryInterface::class)) {
        $services
            ->set(BridgeConstantsInterface::SERVICE_LOGGER, LoggerInterface::class)
            ->factory([service(LoggerFactoryInterface::class), 'create'])
            ->args([BridgeConstantsInterface::LOG_CHANNEL]);

        $listener->arg('$logger', service(BridgeConstantsInterface::SERVICE_LOGGER));
    }
};
