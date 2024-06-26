<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyNotification\Bundle\Enum\BundleParam;
use EonX\EasyNotification\Bundle\Enum\ConfigParam;
use EonX\EasyNotification\Bundle\Enum\ConfigServiceId;
use EonX\EasyNotification\Bundle\Enum\ConfigTag;
use EonX\EasyNotification\Client\NotificationClient;
use EonX\EasyNotification\Client\NotificationClientInterface;
use EonX\EasyNotification\Configurator\ProviderHeaderQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\PushBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\QueueUrlQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\RealTimeBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\SignatureQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\SlackBodyQueueMessageConfigurator;
use EonX\EasyNotification\Configurator\TypeQueueMessageConfigurator;
use EonX\EasyNotification\Factory\QueueTransportFactoryInterface;
use EonX\EasyNotification\Factory\SqsQueueTransportFactory;
use EonX\EasyNotification\Provider\CachedConfigProvider;
use EonX\EasyNotification\Provider\ConfigProvider;
use EonX\EasyNotification\Provider\ConfigProviderInterface;
use EonX\EasyNotification\Provider\SubscribeInfoProvider;
use EonX\EasyNotification\Provider\SubscribeInfoProviderInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // SubscribeInfoProvider
    $services
        ->set(SubscribeInfoProviderInterface::class, SubscribeInfoProvider::class)
        ->arg('$apiUrl', param(ConfigParam::ApiUrl->value));

    // Config + ConfigProvider
    $services
        ->set(ConfigProviderInterface::class, ConfigProvider::class)
        ->arg('$apiUrl', param(ConfigParam::ApiUrl->value));

    $services->set(ConfigServiceId::ConfigCache->value, ArrayAdapter::class);

    $services
        ->set(CachedConfigProvider::class)
        ->decorate(ConfigProviderInterface::class)
        ->arg('$cache', service(ConfigServiceId::ConfigCache->value))
        ->arg('$expiresAfter', param(ConfigParam::ConfigCacheExpiresAfter->value))
        ->arg('$key', BundleParam::ConfigCacheKey->value);

    // Client
    $services
        ->set(NotificationClientInterface::class, NotificationClient::class)
        ->arg('$configurators', tagged_iterator(ConfigTag::QueueMessageConfigurator->value));

    // Configurators
    $services->set(PushBodyQueueMessageConfigurator::class);
    $services->set(RealTimeBodyQueueMessageConfigurator::class);
    $services->set(SlackBodyQueueMessageConfigurator::class);
    $services->set(ProviderHeaderQueueMessageConfigurator::class);
    $services->set(QueueUrlQueueMessageConfigurator::class);
    $services->set(TypeQueueMessageConfigurator::class);
    $services->set(SignatureQueueMessageConfigurator::class);

    // SQS Queue
    $services->set(QueueTransportFactoryInterface::class, SqsQueueTransportFactory::class);
};
