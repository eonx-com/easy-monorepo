<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Aws\Sqs\SqsClient;
use EonX\EasyNotification\Bridge\BridgeConstantsInterface;
use EonX\EasyNotification\Config\CacheConfigFinder;
use EonX\EasyNotification\Config\Config;
use EonX\EasyNotification\Config\ConfigFinder;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;
use EonX\EasyNotification\Interfaces\QueueTransportInterface;
use EonX\EasyNotification\Interfaces\SqsClientFactoryInterface;
use EonX\EasyNotification\NotificationClient;
use EonX\EasyNotification\Queue\SqsClientFactory;
use EonX\EasyNotification\Queue\SqsQueueTransport;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Config + ConfigFinder
    $services->set(ConfigFinderInterface::class, ConfigFinder::class);

    $services->set(BridgeConstantsInterface::SERVICE_CONFIG_CACHE, ArrayAdapter::class);

    $services
        ->set(CacheConfigFinder::class)
        ->decorate(ConfigFinderInterface::class)
        ->arg('$cache', ref(BridgeConstantsInterface::SERVICE_CONFIG_CACHE))
        ->arg('$expiresAfter', '%' . BridgeConstantsInterface::PARAM_CONFIG_CACHE_EXPIRES_AFTER . '%');

    $services
        ->set(ConfigInterface::class, Config::class)
        ->factory([ref(ConfigFinderInterface::class), 'find']);

    // Client
    $services
        ->set(NotificationClientInterface::class, NotificationClient::class)
        ->arg('$configurators', tagged_iterator(BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR));

    // SQS Queue
    $services->set(SqsClientFactoryInterface::class, SqsClientFactory::class);

    $services
        ->set(BridgeConstantsInterface::SERVICE_SQS_CLIENT, SqsClient::class)
        ->factory([ref(SqsClientFactoryInterface::class), 'create']);

    $services
        ->set(QueueTransportInterface::class, SqsQueueTransport::class)
        ->arg('$sqs', ref(BridgeConstantsInterface::SERVICE_SQS_CLIENT));
};
