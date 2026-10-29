<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Laravel;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyRequestId\Common\Provider\RequestIdProviderInterface;
use EonX\EasyRequestResponseLogging\Bundle\Enum\BundleParam;
use EonX\EasyRequestResponseLogging\Bundle\Enum\ConfigServiceId;
use EonX\EasyRequestResponseLogging\Common\Builder\RequestResponseDataBuilder;
use EonX\EasyRequestResponseLogging\Common\Logger\RequestResponseLogger;
use EonX\EasyRequestResponseLogging\Laravel\Middleware\RequestResponseLoggingMiddleware;
use EonX\EasyUtils\SensitiveData\Sanitizer\SensitiveDataSanitizerInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use LogicException;
use Psr\Log\LoggerInterface;

final class EasyRequestResponseLoggingServiceProvider extends ServiceProvider
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-request-response-logging.php' => \base_path(
                'config/easy-request-response-logging.php'
            ),
        ]);

        if ($this->isEnabled() === false) {
            return;
        }

        if ($this->app->bound(RequestIdProviderInterface::class) === false) {
            throw new LogicException('EasyRequestIdServiceProvider is not registered.');
        }

        if ($this->app->bound(SensitiveDataSanitizerInterface::class) === false) {
            throw new LogicException(
                'SensitiveDataSanitizerInterface is not registered, register EasyUtilsServiceProvider and keep'
                . ' easy-utils.sensitive_data.enabled'
            );
        }

        // Laravel: prepended to be the outermost global middleware, so the duration covers the whole pipeline
        if ($this->app->bound(HttpKernel::class)) {
            $kernel = $this->app->make(HttpKernel::class);

            if ($kernel instanceof FoundationHttpKernel) {
                $kernel->prependMiddleware(RequestResponseLoggingMiddleware::class);
            }
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/easy-request-response-logging.php',
            'easy-request-response-logging'
        );

        if ($this->isEnabled() === false) {
            return;
        }

        $this->app->singleton(
            ConfigServiceId::Logger->value,
            static function (Container $app): LoggerInterface {
                if (\interface_exists(LoggerFactoryInterface::class) && $app->bound(LoggerFactoryInterface::class)) {
                    return $app->make(LoggerFactoryInterface::class)
                        ->create(BundleParam::LogChannel->value);
                }

                return $app->make(LoggerInterface::class);
            }
        );

        $this->app->singleton(
            RequestResponseDataBuilder::class,
            static fn (Container $app): RequestResponseDataBuilder => new RequestResponseDataBuilder(
                sensitiveDataSanitizer: $app->make(SensitiveDataSanitizerInterface::class),
                requestIdProvider: $app->make(RequestIdProviderInterface::class),
                logRequestBody: (bool)\config('easy-request-response-logging.log_request_body', true),
                logResponseBody: (bool)\config('easy-request-response-logging.log_response_body', true),
                maxBodySize: (int)\config('easy-request-response-logging.max_body_size', 32768),
                maxRawBodySize: (int)\config('easy-request-response-logging.max_raw_body_size', 1048576)
            )
        );

        $this->app->singleton(
            RequestResponseLogger::class,
            static fn (Container $app): RequestResponseLogger => new RequestResponseLogger(
                logger: $app->make(ConfigServiceId::Logger->value),
                requestResponseDataBuilder: $app->make(RequestResponseDataBuilder::class),
                doNotLogPaths: (array)\config('easy-request-response-logging.do_not_log_paths', [])
            )
        );

        $this->app->singleton(RequestResponseLoggingMiddleware::class);

        if ($this->app instanceof LumenApplication) {
            $this->app->middleware([
                RequestResponseLoggingMiddleware::class,
            ]);
        }
    }

    private function isEnabled(): bool
    {
        return (bool)\config('easy-request-response-logging.enabled', false);
    }
}
