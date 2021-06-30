<?php

declare(strict_types=1);

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Bridge\Symfony\Session\SessionTrackingSubscriber;
use EonX\EasyBugsnag\Session\SessionTracker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    $services
        ->set(SessionTracker::class)
        ->arg('$exclude', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE . '%')
        ->arg('$excludeDelimiter', '%' . BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_DELIMITER . '%');

    $services->set(SessionTrackingSubscriber::class);
};
