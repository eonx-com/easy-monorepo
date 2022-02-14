<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use EonX\EasyNotification\Config\CacheConfigFinder;
use EonX\EasyNotification\Config\ConfigFinder;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\Interfaces\QueueTransportFactoryInterface;
use EonX\EasyNotification\Interfaces\SubscribeInfoFinderInterface;
use EonX\EasyNotification\NotificationClient;
use EonX\EasyNotification\Queue\Configurators\ProviderHeaderConfigurator;
use EonX\EasyNotification\Queue\Configurators\PushBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\QueueUrlConfigurator;
use EonX\EasyNotification\Queue\Configurators\RealTimeBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\SignatureConfigurator;
use EonX\EasyNotification\Queue\Configurators\SlackBodyConfigurator;
use EonX\EasyNotification\Queue\Configurators\TypeConfigurator;
use EonX\EasyNotification\Queue\SqsQueueTransportFactory;
use EonX\EasyNotification\Subscribe\SubscribeInfoFinder;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // SubscribeInfoFinder
    $services
        ->set(SubscribeInfoFinderInterface::class, SubscribeInfoFinder::class)
        ->arg('$apiUrl', '%' . BridgeConstantsInterface::PARAM_API_URL . '%');

    // Config + ConfigFinder
    $services
        ->set(ConfigFinderInterface::class, ConfigFinder::class)
        ->arg('$apiUrl', '%' . BridgeConstantsInterface::PARAM_API_URL . '%');

    $services->set(BridgeConstantsInterface::SERVICE_CONFIG_CACHE, ArrayAdapter::class);

    $services
        ->set(CacheConfigFinder::class)
        ->decorate(ConfigFinderInterface::class)
        ->arg('$cache', ref(BridgeConstantsInterface::SERVICE_CONFIG_CACHE))
        ->arg('$expiresAfter', '%' . BridgeConstantsInterface::PARAM_CONFIG_CACHE_EXPIRES_AFTER . '%');

    // Client
    $services
        ->set(NotificationClientInterface::class, NotificationClient::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR));

    // Configurators
    $services->set(PushBodyConfigurator::class);
    $services->set(RealTimeBodyConfigurator::class);
    $services->set(SlackBodyConfigurator::class);
    $services->set(ProviderHeaderConfigurator::class);
    $services->set(QueueUrlConfigurator::class);
    $services->set(TypeConfigurator::class);
    $services->set(SignatureConfigurator::class);

    // SQS Queue
    $services->set(QueueTransportFactoryInterface::class, SqsQueueTransportFactory::class);
};
