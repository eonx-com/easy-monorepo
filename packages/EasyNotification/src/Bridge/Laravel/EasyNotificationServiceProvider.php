<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Bridge\Laravel;

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
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class EasyNotificationServiceProvider extends ServiceProvider
{
    /**
     * @var string[]
     */
    protected static array $configurators = [
        PushBodyConfigurator::class,
        RealTimeBodyConfigurator::class,
        SlackBodyConfigurator::class,
        ProviderHeaderConfigurator::class,
        QueueUrlConfigurator::class,
        TypeConfigurator::class,
        SignatureConfigurator::class,
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
            ConfigFinderInterface::class,
            static fn (): ConfigFinderInterface => new ConfigFinder(\config('easy-notification.api_url'))
        );

        $this->app->singleton(BridgeConstantsInterface::SERVICE_CONFIG_CACHE, ArrayAdapter::class);

        $this->app->extend(
            ConfigFinderInterface::class,
            static fn (
                ConfigFinderInterface $decorated,
                Container $app,
            ): ConfigFinderInterface => new CacheConfigFinder(
                $app->make(BridgeConstantsInterface::SERVICE_CONFIG_CACHE),
                $decorated,
                \config(
                    'easy-notification.config_expires_after',
                    BridgeConstantsInterface::CONFIG_CACHE_EXPIRES_AFTER
                )
            )
        );

        // SubscribeInfoFinder
        $this->app->singleton(
            SubscribeInfoFinderInterface::class,
            static fn (): SubscribeInfoFinderInterface => new SubscribeInfoFinder(
                \config('easy-notification.api_url')
            )
        );

        // Client
        $this->app->singleton(
            NotificationClientInterface::class,
            static fn (Container $app): NotificationClientInterface => new NotificationClient(
                $app->tagged(BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR),
                $app->make(QueueTransportFactoryInterface::class)
            )
        );

        // Configurators
        foreach (static::$configurators as $configurator) {
            $this->app->singleton($configurator);
            $this->app->tag($configurator, [BridgeConstantsInterface::TAG_QUEUE_MESSAGE_CONFIGURATOR]);
        }

        // SQS Queue
        $this->app->singleton(QueueTransportFactoryInterface::class, SqsQueueTransportFactory::class);
    }
}
