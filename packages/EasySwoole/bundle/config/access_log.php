<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Bundle\Enum\ConfigServiceId;
use EonX\EasySwoole\Common\Listener\AccessLogListener;
use EonX\EasySwoole\Logging\Factory\MonologLoggerFactory;
use EonX\EasySwoole\Logging\Formatter\HttpFoundationAccessLogFormatter;
use EonX\EasySwoole\Logging\Formatter\HttpFoundationAccessLogFormatterInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Formatter
    $services->set(HttpFoundationAccessLogFormatterInterface::class, HttpFoundationAccessLogFormatter::class);

    // Logger
    $loggerServiceId = LoggerInterface::class;

    if (\class_exists(Logger::class)) {
        $loggerServiceId = ConfigServiceId::AccessLogLogger->value;

        $services
            ->set(MonologLoggerFactory::class)
            ->arg('$timezone', param(ConfigParam::AccessLogTimezone->value));

        $services
            ->set($loggerServiceId, LoggerInterface::class)
            ->factory([service(MonologLoggerFactory::class), 'create']);
    }

    // Listener
    $services
        ->set(AccessLogListener::class)
        ->arg('$logger', service($loggerServiceId))
        ->arg('$doNotLogPaths', param(ConfigParam::AccessLogDoNotLogPaths->value))
        ->tag('kernel.event_listener');
};
