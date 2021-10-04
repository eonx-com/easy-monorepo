<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Laravel;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Config\StreamHandlerConfigProvider;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use EonX\EasyLogging\LoggerFactory;
use EonX\EasyUtils\CollectorHelper;
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

                // Add default stream handler only if no handler config providers
                $handlerConfigProviders = $app->tagged(BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER);
                $handlerConfigProviders = CollectorHelper::convertToArray($handlerConfigProviders);

                if (\config('easy-logging.stream_handler', true) && \count($handlerConfigProviders) < 1) {
                    $level = \config('easy-logging.stream_handler_level')
                        ? (int)\config('easy-logging.stream_handler_level')
                        : null;

                    $handlerConfigProviders = [new StreamHandlerConfigProvider(null, $level)];
                }

                $factory
                    ->setHandlerConfigProviders($handlerConfigProviders)
                    ->setLoggerConfigurators($app->tagged(BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR))
                    ->setProcessorConfigProviders(
                        $app->tagged(BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER)
                    );

                return $factory;
            }
        );

        // Override default logger only if enabled
        if (\config('easy-logging.override_default_logger', true) === false) {
            return;
        }

        // Override PSR Logger
        $this->app->singleton(
            LoggerInterface::class,
            static function (Container $app, ?array $params = null): LoggerInterface {
                $channel = $params[BridgeConstantsInterface::KEY_CHANNEL] ?? \config('easy-logging.default_channel');

                return $app->make(LoggerFactoryInterface::class)->create($channel);
            }
        );

        // Override default logger alias
        $this->app->alias(LoggerInterface::class, 'logger');
    }
}
