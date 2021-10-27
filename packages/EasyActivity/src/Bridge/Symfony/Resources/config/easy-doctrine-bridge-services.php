<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use EonX\EasyActivity\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriber;
use EonX\EasyActivity\Bridge\EasyDoctrine\EasyDoctrineEntityEventsSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EasyDoctrineEntityEventsSubscriberInterface::class, EasyDoctrineEntityEventsSubscriber::class)
        ->arg('$enabled', '%' . BridgeConstantsInterface::PARAM_EASY_DOCTRINE_SUBSCRIBER_ENABLED . '%')
        ->tag('kernel.event_subscriber');
};
