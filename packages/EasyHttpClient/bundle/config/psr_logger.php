<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bundle\Enum\BundleParam;
use EonX\EasyHttpClient\PsrLogger\Listener\LogHttpRequestSentListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(LogHttpRequestSentListener::class)
        ->tag('kernel.event_listener')
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);
};
