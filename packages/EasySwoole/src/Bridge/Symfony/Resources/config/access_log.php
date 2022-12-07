<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\AccessLog\HttpFoundationAccessLogFormatter;
use EonX\EasySwoole\AccessLog\MonologLoggerFactory;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Symfony\Listeners\AccessLogListener;
use EonX\EasySwoole\Interfaces\HttpFoundationAccessLogFormatterInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Formatter
    $services->set(HttpFoundationAccessLogFormatterInterface::class, HttpFoundationAccessLogFormatter::class);

    // Logger
    $services
        ->set(MonologLoggerFactory::class)
        ->arg('$timezone', param(BridgeConstantsInterface::PARAM_ACCESS_LOG_TIMEZONE));

    $services
        ->set(BridgeConstantsInterface::SERVICE_ACCESS_LOG_LOGGER, LoggerInterface::class)
        ->factory([service(MonologLoggerFactory::class), 'create']);

    // Listener
    $services
        ->set(AccessLogListener::class)
        ->arg('$logger', service(BridgeConstantsInterface::SERVICE_ACCESS_LOG_LOGGER))
        ->arg('$doNotLogPaths', param(BridgeConstantsInterface::PARAM_ACCESS_LOG_DO_NOT_LOG_PATHS))
        ->tag('kernel.event_listener');
};
