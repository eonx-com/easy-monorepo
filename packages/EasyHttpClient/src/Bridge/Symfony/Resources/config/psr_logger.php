<?php

declare(strict_types=1);

use EonX\EasyHttpClient\Bridge\BridgeConstantsInterface;
use EonX\EasyHttpClient\Bridge\PsrLogger\LogHttpRequestSentListener;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

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
            ->set(BridgeConstantsInterface::SERVICE_LOGGER)
            ->factory([ref(LoggerFactoryInterface::class), 'create'])
            ->args([BridgeConstantsInterface::LOG_CHANNEL]);

        $listener->arg('$logger', ref(BridgeConstantsInterface::SERVICE_LOGGER));
    }
};
