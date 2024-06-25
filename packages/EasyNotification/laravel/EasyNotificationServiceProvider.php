<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Laravel;

use EonX\EasyNotification\Bundle\Enum\BundleParam;
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
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class EasyNotificationServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected static array $configurators = [
        PushBodyQueueMessageConfigurator::class,
        RealTimeBodyQueueMessageConfigurator::class,
        SlackBodyQueueMessageConfigurator::class,
        ProviderHeaderQueueMessageConfigurator::class,
        QueueUrlQueueMessageConfigurator::class,
        TypeQueueMessageConfigurator::class,
        SignatureQueueMessageConfigurator::class,
    ];

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-notification.php' => \base_path('config/easy-notification.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-notification.php', 'easy-notification');

        // Config + ConfigFinder
        $this->app->singleton(
            ConfigProviderInterface::class,
            static fn (): ConfigProviderInterface => new ConfigProvider(\config('easy-notification.api_url'))
        );

        $this->app->singleton(ConfigServiceId::ConfigCache->value, ArrayAdapter::class);

        $this->app->extend(
            ConfigProviderInterface::class,
            static fn (
                ConfigProviderInterface $decorated,
                Container $app,
            ): ConfigProviderInterface => new CachedConfigProvider(
                $app->make(ConfigServiceId::ConfigCache->value),
                $decorated,
                \config('easy-notification.config_expires_after', 3600),
                BundleParam::ConfigCacheKey->value,
            )
        );

        // SubscribeInfoFinder
        $this->app->singleton(
            SubscribeInfoProviderInterface::class,
            static fn (): SubscribeInfoProviderInterface => new SubscribeInfoProvider(
                \config('easy-notification.api_url')
            )
        );

        // Client
        $this->app->singleton(
            NotificationClientInterface::class,
            static fn (Container $app): NotificationClientInterface => new NotificationClient(
                $app->tagged(ConfigTag::QueueMessageConfigurator->value),
                $app->make(QueueTransportFactoryInterface::class)
            )
        );

        // Configurators
        foreach (static::$configurators as $configurator) {
            $this->app->singleton($configurator);
            $this->app->tag($configurator, [ConfigTag::QueueMessageConfigurator->value]);
        }

        // SQS Queue
        $this->app->singleton(QueueTransportFactoryInterface::class, SqsQueueTransportFactory::class);
    }
}
