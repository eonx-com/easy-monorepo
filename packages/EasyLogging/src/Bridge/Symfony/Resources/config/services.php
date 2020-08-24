<?php

declare(strict_types=1);

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasyLogging\LoggerFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(LoggerFactoryInterface::class, LoggerFactory::class)
        ->args(['%' . BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL . '%'])
        ->call('setHandlerConfigProviders', [tagged_iterator(BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER)])
        ->call('setLoggerConfigurators', [tagged_iterator(BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR)])
        ->call(
            'setProcessorConfigProviders',
            [tagged_iterator(BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER)]
        );

    $services
        ->set(LoggerInterface::class)
        ->factory([ref(LoggerFactoryInterface::class), 'create'])
        ->args(['%' . BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL . '%']);

    $services->alias('logger', LoggerInterface::class);
};
