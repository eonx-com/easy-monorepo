<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Dedicated logger so app metrics cannot be dropped by the application logging configuration.
    // It requires Monolog 3, see AppMetricLoggerFallbackCompilerPass for when it is not installed
    if (AppMetricLoggerFactory::isSupported()) {
        $services
            ->set(ConfigServiceId::AppMetricLogger->value, LoggerInterface::class)
            ->factory([inline_service(AppMetricLoggerFactory::class), 'create']);
    }

    $services
        ->set(AppMetricClientInterface::class, AppMetricClient::class)
        ->arg('$logger', service(ConfigServiceId::AppMetricLogger->value)->ignoreOnInvalid())
        ->arg('$namespace', param(ConfigParam::AppMetricNamespace->value));
};
