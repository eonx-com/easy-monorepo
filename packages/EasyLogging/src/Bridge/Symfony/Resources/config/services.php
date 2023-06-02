<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasyLogging\LoggerFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(LoggerFactoryInterface::class, LoggerFactory::class)
        ->arg('$defaultChannel', '%' . BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL . '%')
        ->arg('$loggerClass', '%' . BridgeConstantsInterface::PARAM_LOGGER_CLASS . '%')
        ->call('setHandlerConfigProviders', [tagged_iterator(BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER)])
        ->call('setLoggerConfigurators', [tagged_iterator(BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR)])
        ->call(
            'setProcessorConfigProviders',
            [tagged_iterator(BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER)]
        );

    $services
        ->set('easy_logging.logger', '%' . BridgeConstantsInterface::PARAM_LOGGER_CLASS . '%')
        ->factory([service(LoggerFactoryInterface::class), 'create'])
        ->args(['%' . BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL . '%']);
};
