<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigParam;
use EonX\EasyRequestResponseLogging\Common\Subscriber\RequestResponseLoggingSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RequestResponseLoggingSubscriber::class)
        ->arg('$doNotLogPaths', param(ConfigParam::DoNotLogPaths->value))
        ->arg('$logRequestBody', param(ConfigParam::LogRequestBody->value))
        ->arg('$logResponseBody', param(ConfigParam::LogResponseBody->value))
        ->arg('$maxBodySize', param(ConfigParam::MaxBodySize->value))
        ->arg('$maxRawBodySize', param(ConfigParam::MaxRawBodySize->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
