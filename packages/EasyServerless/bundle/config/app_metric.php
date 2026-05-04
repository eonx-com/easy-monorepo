<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(AppMetricClientInterface::class, AppMetricClient::class)
        ->arg('$logger', service(LoggerInterface::class)->ignoreOnInvalid())
        ->arg('$namespace', param(ConfigParam::AppMetricNamespace->value));
};
