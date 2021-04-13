<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Laravel;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Config\StreamHandlerConfigProvider;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasyLogging\LoggerFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class EasyLoggingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-logging.php' => \base_path('config/easy-logging.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-logging.php', 'easy-logging');

        $this->app->singleton(
            LoggerFactoryInterface::class,
            static function (Container $app): LoggerFactoryInterface {
                $factory = new LoggerFactory(\config('easy-logging.default_channel'));

                $factory
                    ->setHandlerConfigProviders($app->tagged(BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER))
                    ->setLoggerConfigurators($app->tagged(BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR))
                    ->setProcessorConfigProviders(
                        $app->tagged(BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER)
                    );

                return $factory;
            }
        );

        // Override PSR Logger
        $this->app->singleton(LoggerInterface::class, static function (Container $app): LoggerInterface {
            return $app->make(LoggerFactoryInterface::class)->create(\config('easy-logging.default_channel'));
        });

        // Override default logger alias
        $this->app->alias(LoggerInterface::class, 'logger');

        // Default stream handler
        if (\config('easy-logging.stream_handler', true)) {
            $this->app->singleton(StreamHandlerConfigProvider::class, static function (): StreamHandlerConfigProvider {
                $level = \config('easy-logging.stream_handler_level')
                    ? (int)\config('easy-logging.stream_handler_level')
                    : null;

                return new StreamHandlerConfigProvider(null, $level);
            });
            $this->app->tag(
                StreamHandlerConfigProvider::class,
                [BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER]
            );
        }
    }
}
