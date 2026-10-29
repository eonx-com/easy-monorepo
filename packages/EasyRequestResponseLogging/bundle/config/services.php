<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigParam;
use EonX\EasyRequestResponseLogging\Common\Builder\RequestResponseDataBuilder;
use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use EonX\EasyRequestResponseLogging\Common\Subscriber\RequestResponseLoggingSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RequestResponseDataBuilder::class)
        ->arg('$logRequestBody', param(ConfigParam::LogRequestBody->value))
        ->arg('$logResponseBody', param(ConfigParam::LogResponseBody->value))
        ->arg('$maxBodySize', param(ConfigParam::MaxBodySize->value))
        ->arg('$maxRawBodySize', param(ConfigParam::MaxRawBodySize->value));

    $services
        ->set(RequestResponseLogger::class)
        ->arg('$doNotLogPaths', param(ConfigParam::DoNotLogPaths->value))
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    $services->set(RequestResponseLoggingSubscriber::class);
};
