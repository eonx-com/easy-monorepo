<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Pagination\StatelessPagination;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use EonX\EasyServerless\Health\Checker\SanityChecker;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Container\Container;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Psr\Log\LoggerInterface;
use ReflectionProperty;

final class EasyServerlessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-serverless.php' => \base_path('config/easy-serverless.php'),
        ]);

        if (\config('easy-serverless.health.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/routes/health.php');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-serverless.php', 'easy-serverless');

        $this->registerAppMetric();
        $this->registerDatadog();
        $this->registerHealth();

        if (\class_exists(PaginationInterface::class)) {
            $this->registerPagination();
        }

        $this->app->bind(SqsHandler::class, static function (Container $app): SqsHandler {
            $config = $app->make('config');
            $connectionName = $config->get('queue.default', 'sqs');
            $getQueueConfig = static fn (string $name, mixed $default): mixed
                => $config->get(
                    \sprintf('queue.connections.%s.%s', $connectionName, $name),
                    $default
                );

            return new SqsHandler(
                container: $app,
                connectionName: $connectionName,
                appMaxRetries: $getQueueConfig('max_retries', 3),
                timeoutThresholdMilliseconds: $getQueueConfig('timeout_threshold_ms', 1000),
                partialBatchFailure: $getQueueConfig('partial_batch_failure', false),
                stateCheckers: \config('easy-serverless.state.check', true)
                    ? $app->tagged(ConfigTag::StateChecker->value)
                    : [],
            );
        });
    }

    private function registerAppMetric(): void
    {
        if (\config('easy-serverless.app_metric.enabled', true) === false) {
            return;
        }

        $this->app->singleton(
            AppMetricClientInterface::class,
            static fn (Container $app): AppMetricClientInterface => new AppMetricClient(
                $app->make(LoggerInterface::class),
                \config('easy-serverless.app_metric.namespace')
            )
        );
    }

    private function registerDatadog(): void
    {
        if (\config('easy-serverless.datadog.enabled', true) === false) {
            return;
        }

        // Add PHP runtime information to the Datadog root span of requests, queue jobs and console commands,
        // a no-op when the ddtrace extension is not loaded
        $this->app->make('events')
            ->listen(
                [JobProcessing::class, CommandStarting::class],
                static function (): void {
                    RootSpanHelper::addPhpRuntimeInfo();
                }
            );

        // Requests are covered by a global middleware rather than RouteMatched, so unmatched routes (404/405)
        // and requests short-circuited by other global middleware (e.g. maintenance mode) are enriched too
        if ($this->app instanceof LumenApplication) {
            // Lumen dispatches CommandStarting only when not running in console, so console commands never get it.
            // The CLI root span covers the whole process, enrich it right away
            if ($this->app->runningInConsole()) {
                RootSpanHelper::addPhpRuntimeInfo();
            }

            // Lumen only appends global middleware and the provider is usually registered after the application
            // middleware, so put it first to run before any middleware that could return early. Lumen has no API
            // to prepend, its global middleware is kept in a protected property
            $middlewareProperty = new ReflectionProperty(LumenApplication::class, 'middleware');
            /** @var array<int, string> $globalMiddleware */
            $globalMiddleware = $middlewareProperty->getValue($this->app);

            if (\in_array(PhpRuntimeInfoMiddleware::class, $globalMiddleware, true) === false) {
                $middlewareProperty->setValue($this->app, [PhpRuntimeInfoMiddleware::class, ...$globalMiddleware]);
            }

            return;
        }

        // The HTTP kernel is already resolved when providers are registered during an HTTP request,
        // waiting for its resolution avoids building it in console commands and queue workers
        $this->callAfterResolving(HttpKernel::class, static function (HttpKernel $httpKernel): void {
            if ($httpKernel instanceof FoundationHttpKernel) {
                // Prepend so it runs before maintenance mode and any other global middleware
                $httpKernel->prependMiddleware(PhpRuntimeInfoMiddleware::class);
            }
        });
    }

    private function registerHealth(): void
    {
        if (\config('easy-serverless.health.enabled', true) === false) {
            return;
        }

        // Checkers
        $this->app->singleton(SanityChecker::class);
        $this->app->tag(
            [SanityChecker::class],
            [ConfigTag::HealthChecker->value]
        );

        // Aggregated Checker
        $this->app->singleton(
            AggregatedHealthChecker::class,
            static fn (Container $app): AggregatedHealthChecker => new AggregatedHealthChecker(
                $app->tagged(ConfigTag::HealthChecker->value),
                $app->make(LoggerInterface::class)
            )
        );

        // Controller
        $this->app->singleton(HealthCheckController::class);
    }

    private function registerPagination(): void
    {
        $this->app->singleton(
            PaginationInterface::class,
            static fn (Container $app): PaginationInterface => new StatelessPagination(
                $app->make(PaginationProviderInterface::class)
            )
        );
    }
}
