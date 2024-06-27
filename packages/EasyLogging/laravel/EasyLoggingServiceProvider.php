<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Laravel;

use EonX\EasyLogging\Bundle\Enum\BundleParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Factory\LoggerFactory;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Processor\SensitiveDataSanitizerProcessor;
use EonX\EasyLogging\Provider\StreamHandlerConfigProvider;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use LogicException;
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
                $factory = new LoggerFactory(
                    defaultChannel: \config('easy-logging.default_channel'),
                    lazyLoggers: (array)\config('easy-logging.lazy_loggers', []),
                );

                // Add default stream handler only if no handler config providers
                $handlerConfigProviders = $app->tagged(ConfigTag::HandlerConfigProvider->value);
                $handlerConfigProviders = CollectorHelper::convertToArray($handlerConfigProviders);

                if (\config('easy-logging.stream_handler', true) && \count($handlerConfigProviders) < 1) {
                    $level = \config('easy-logging.stream_handler_level')
                        ? (int)\config('easy-logging.stream_handler_level')
                        : null;

                    $handlerConfigProviders = [new StreamHandlerConfigProvider(null, $level)];
                }

                $factory
                    ->setHandlerConfigProviders($handlerConfigProviders)
                    ->setLoggerConfigurators($app->tagged(ConfigTag::LoggerConfigurator->value))
                    ->setProcessorConfigProviders(
                        $app->tagged(ConfigTag::ProcessorConfigProvider->value)
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
                $channel = $params[BundleParam::KeyChannel->value] ?? \config('easy-logging.default_channel');

                return $app->make(LoggerFactoryInterface::class)->create($channel);
            }
        );

        // Override default logger alias
        $this->app->alias(LoggerInterface::class, 'logger');

        // Sensitive data sanitizer
        if (\config('easy-logging.sensitive_data_sanitizer.enabled', false)) {
            $this->app->singleton(
                SensitiveDataSanitizerProcessor::class,
                static function (Container $app): SensitiveDataSanitizerProcessor {
                    $sanitizerId = SensitiveDataSanitizerInterface::class;

                    if (\interface_exists($sanitizerId) === false || $app->has($sanitizerId) === false) {
                        throw new LogicException(
                            'To use sensitive data sanitization, the package eonx-com/easy-utils must be installed,
                            and its service provider must be registered'
                        );
                    }

                    return new SensitiveDataSanitizerProcessor($app->make($sanitizerId));
                }
            );
        }
    }
}
