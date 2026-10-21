<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigParam;
use EonX\EasyRequestResponseLogging\Common\Listener\RequestResponseLoggingListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(RequestResponseLoggingListener::class)
        ->arg('$doNotLogPaths', param(ConfigParam::DoNotLogPaths->value))
        ->arg('$logRequestBody', param(ConfigParam::LogRequestBody->value))
        ->arg('$logResponseBody', param(ConfigParam::LogResponseBody->value))
        ->arg('$maxBodySize', param(ConfigParam::MaxBodySize->value))
        ->arg('$maxRawBodySize', param(ConfigParam::MaxRawBodySize->value))
        ->tag('kernel.event_listener', [
            'event' => RequestEvent::class,
            'method' => 'onKernelRequest',
            'priority' => 1024,
        ])
        ->tag('kernel.event_listener', [
            'event' => ResponseEvent::class,
            'method' => 'onKernelResponse',
            'priority' => -1024,
        ])
        ->tag('kernel.event_listener', [
            'event' => TerminateEvent::class,
            'method' => 'onKernelTerminate',
            'priority' => -1024,
        ])
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
