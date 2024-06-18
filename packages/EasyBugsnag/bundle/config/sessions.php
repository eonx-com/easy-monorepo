<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Bundle\Enum\ConfigServiceId;
use EonX\EasyBugsnag\Configurator\SessionTrackingClientConfigurator;
use EonX\EasyBugsnag\Listener\SessionTrackingListener;
use EonX\EasyBugsnag\Tracker\SessionTracker;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Cache
    $services
        ->set(ConfigServiceId::SessionTrackingCache->value, PhpFilesAdapter::class)
        ->arg('$namespace', param(ConfigParam::SessionTrackingCacheNamespace->value))
        ->arg('$directory', param(ConfigParam::SessionTrackingCacheDirectory->value))
        ->tag('monolog.logger', ['channel' => 'cache']);

    $services
        ->set(SessionTracker::class)
        ->arg('$exclude', param(ConfigParam::SessionTrackingExcludeUrls->value))
        ->arg('$excludeDelimiter', param(ConfigParam::SessionTrackingExcludeUrlsDelimiter->value));

    $services
        ->set(SessionTrackingClientConfigurator::class)
        ->arg('$cache', service(ConfigServiceId::SessionTrackingCache->value))
        ->arg('$expiresAfter', param(ConfigParam::SessionTrackingCacheExpiresAfter->value));

    $services
        ->set(SessionTrackingListener::class)
        ->tag('kernel.event_listener');
};
