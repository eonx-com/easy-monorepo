<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyHttpClient\Bridge\EasyBugsnag\HttpRequestSentBreadcrumbListener;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(HttpRequestSentBreadcrumbListener::class)
        ->tag('kernel.event_listener');
};
