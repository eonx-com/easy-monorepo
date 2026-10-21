<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Common\Listener\RequestResponseLoggingListener;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(ConfigServiceId::Logger->value, LoggerInterface::class)
        ->factory([service(LoggerFactoryInterface::class), 'create'])
        ->args([BundleParam::LogChannel->value]);

    $services
        ->get(RequestResponseLoggingListener::class)
        ->arg('$logger', service(ConfigServiceId::Logger->value));
};
