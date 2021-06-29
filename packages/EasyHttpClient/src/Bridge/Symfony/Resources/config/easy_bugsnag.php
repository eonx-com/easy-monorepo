<?php

declare(strict_types=1);

use EonX\EasyHttpClient\Bridge\EasyBugsnag\HttpRequestSentBreadcrumbListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(HttpRequestSentBreadcrumbListener::class)
        ->tag('kernel.event_listener');
};
