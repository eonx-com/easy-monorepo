<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use Bref\Bref;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Pagination\StatelessPagination;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\AppMetric\Factory\AppMetricLoggerFactory;
use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Bundle\Enum\ConfigServiceId;
use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use EonX\EasyServerless\Health\Checker\SanityChecker;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Laravel\Middleware\PhpRuntimeInfoMiddleware;
use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use EonX\EasyServerless\Laravel\Subscribers\LazySnapStartLifecycleSubscriber;
use EonX\EasyServerless\Laravel\Warmers\DatabaseConnectionsWarmer;
use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\SnapStart\Subscriber\SnapStartLifecycleSubscriber;
use EonX\EasyServerless\SnapStart\Warmer\ClassMapWarmer;
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
        $this->registerSnapStart();

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

    /**
     * Container::tagged() creates the services of a tag in a single generator, which stops at the first service that
     * cannot be created. With a closure per service, the SnapStartLifecycleSubscriber skips it and goes on.
     *
     * @return iterable<string, \Closure(): object>
     */
    private static function createTaggedServiceClosures(Container $app, string $tag): iterable
    {
        /** @var array<string, string[]> $tags */
        $tags = (new ReflectionProperty(Container::class, 'tags'))->getValue($app);

        foreach ($tags[$tag] ?? [] as $abstract) {
            yield $abstract => static fn (): object => $app->make($abstract);
        }
    }

    private function registerAppMetric(): void
    {
        if (\config('easy-serverless.app_metric.enabled', true) === false) {
            return;
        }

        // Dedicated logger so app metrics cannot be dropped by the application logging configuration,
        // it requires Monolog 3 so fallback to the application logger when not installed
        $this->app->singleton(
            ConfigServiceId::AppMetricLogger->value,
            static fn (Container $app): LoggerInterface => AppMetricLoggerFactory::isSupported()
                ? (new AppMetricLoggerFactory())->create()
                : $app->make(LoggerInterface::class)
        );

        $this->app->singleton(
            AppMetricClientInterface::class,
            static fn (Container $app): AppMetricClientInterface => new AppMetricClient(
                $app->make(ConfigServiceId::AppMetricLogger->value),
                \config('easy-serverless.app_metric.namespace')
            )
        );
    }

    private function registerDatadog(): void
    {
        if (\config('easy-serverless.datadog.enabled', true) === false) {
            return;
        }

        $this->app->make('events')
            ->listen(
                [JobProcessing::class, CommandStarting::class],
                static function (): void {
                    RootSpanHelper::addPhpRuntimeInfo();
                }
            );

        if ($this->app instanceof LumenApplication) {
            // Lumen does not dispatch CommandStarting in console, and the CLI root span covers the whole process
            if ($this->app->runningInConsole()) {
                RootSpanHelper::addPhpRuntimeInfo();
            }

            // Run before global middleware that could return early, Lumen has no API to prepend middleware
            $middlewareProperty = new ReflectionProperty(LumenApplication::class, 'middleware');
            /** @var array<int, string> $globalMiddleware */
            $globalMiddleware = $middlewareProperty->getValue($this->app);

            if (\in_array(PhpRuntimeInfoMiddleware::class, $globalMiddleware, true) === false) {
                $middlewareProperty->setValue($this->app, [PhpRuntimeInfoMiddleware::class, ...$globalMiddleware]);
            }

            return;
        }

        // Avoids building the HTTP kernel in console commands and queue workers
        $this->callAfterResolving(HttpKernel::class, static function (HttpKernel $httpKernel): void {
            if ($httpKernel instanceof FoundationHttpKernel) {
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

    private function registerSnapStart(): void
    {
        if ((bool)\config('easy-serverless.snap_start.enabled', false) === false) {
            return;
        }

        if (\config('easy-serverless.snap_start.warmers.class_map.enabled', true)) {
            $this->app->singleton(
                ClassMapWarmer::class,
                static fn (): ClassMapWarmer => new ClassMapWarmer(
                    \config('easy-serverless.snap_start.warmers.class_map.namespaces', ['App\\'])
                )
            );
            $this->app->tag([ClassMapWarmer::class], [SnapStartTag::Warmer->value]);
        }

        $this->app->singleton(DatabaseConnectionsWarmer::class);

        $this->app->singleton(
            SnapStartLifecycleSubscriber::class,
            static fn (Container $app): SnapStartLifecycleSubscriber => new SnapStartLifecycleSubscriber(
                (static function () use ($app): iterable {
                    yield from self::createTaggedServiceClosures($app, SnapStartTag::Warmer->value);

                    // Closes the connections opened during the initialization, it must run last
                    yield DatabaseConnectionsWarmer::class => static fn (): DatabaseConnectionsWarmer
                        => $app->make(DatabaseConnectionsWarmer::class);
                })(),
                self::createTaggedServiceClosures($app, SnapStartTag::AfterRestoreHook->value),
                $app->make(LoggerInterface::class)
            )
        );

        // Subscribed now as Lumen boots the providers on the first request, after the snapshot
        if (LambdaContextHelper::inSnapStartLambda()) {
            Bref::events()->subscribe(new LazySnapStartLifecycleSubscriber($this->app));
        }
    }
}
