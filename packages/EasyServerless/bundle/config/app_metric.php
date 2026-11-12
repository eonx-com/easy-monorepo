<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Dedicated logger so app metrics cannot be dropped by the application logging configuration,
    // it requires Monolog so fallback to the application logger when not installed
    $logger = service(LoggerInterface::class)->ignoreOnInvalid();

    if (\class_exists(Logger::class)) {
        $services
            ->set(ConfigServiceId::AppMetricLogger->value, LoggerInterface::class)
            ->factory([inline_service(AppMetricLoggerFactory::class), 'create']);

        $logger = service(ConfigServiceId::AppMetricLogger->value);
    }

    $services
        ->set(AppMetricClientInterface::class, AppMetricClient::class)
        ->arg('$logger', $logger)
        ->arg('$namespace', param(ConfigParam::AppMetricNamespace->value));
};
