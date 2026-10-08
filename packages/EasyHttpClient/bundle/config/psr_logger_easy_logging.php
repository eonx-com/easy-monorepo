<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bundle\Enum\BundleParam;
use EonX\EasyHttpClient\Bundle\Enum\ConfigServiceId;
use EonX\EasyHttpClient\PsrLogger\Listener\LogHttpRequestSentListener;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
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
        ->get(LogHttpRequestSentListener::class)
        ->arg('$logger', service(ConfigServiceId::Logger->value));
};
