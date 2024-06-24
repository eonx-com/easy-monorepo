<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bundle\Enum\BundleParam;
use EonX\EasyHttpClient\Bundle\Enum\ConfigServiceId;
use EonX\EasyHttpClient\PsrLogger\Listener\LogHttpRequestSentListener;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $listener = $services
        ->set(LogHttpRequestSentListener::class)
        ->tag('kernel.event_listener')
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    if (\interface_exists(LoggerFactoryInterface::class)) {
        $services
            ->set(ConfigServiceId::Logger->value, LoggerInterface::class)
            ->factory([service(LoggerFactoryInterface::class), 'create'])
            ->args([BundleParam::LogChannel->value]);

        $listener->arg('$logger', service(ConfigServiceId::Logger->value));
    }
};
