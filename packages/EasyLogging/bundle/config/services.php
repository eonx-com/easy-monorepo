<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Factory\LoggerFactory;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(LoggerFactoryInterface::class, LoggerFactory::class)
        ->arg('$defaultChannel', '%' . ConfigParam::DefaultChannel->value . '%')
        ->arg('$loggerClass', '%' . ConfigParam::LoggerClass->value . '%')
        ->arg('$lazyLoggers', '%' . ConfigParam::LazyLoggers->value . '%')
        ->call('setHandlerConfigProviders', [tagged_iterator(ConfigTag::HandlerConfigProvider->value)])
        ->call('setLoggerConfigurators', [tagged_iterator(ConfigTag::LoggerConfigurator->value)])
        ->call(
            'setProcessorConfigProviders',
            [tagged_iterator(ConfigTag::ProcessorConfigProvider->value)]
        );

    $services
        ->set('easy_logging.logger', '%' . ConfigParam::LoggerClass->value . '%')
        ->factory([service(LoggerFactoryInterface::class), 'create'])
        ->args(['%' . ConfigParam::DefaultChannel->value . '%']);
};