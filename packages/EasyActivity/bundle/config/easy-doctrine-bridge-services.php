<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use EonX\EasyActivity\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriber;
use EonX\EasyActivity\EasyDoctrine\Subscriber\EasyDoctrineEntityEventsSubscriberInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(EasyDoctrineEntityEventsSubscriberInterface::class, EasyDoctrineEntityEventsSubscriber::class)
        ->arg('$enabled', param(ConfigParam::EasyDoctrineSubscriberEnabled->value))
        ->tag('kernel.event_subscriber');
};
