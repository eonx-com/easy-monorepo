<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Pagination\StatelessPagination;
use EonX\EasyPagination\Provider\PaginationProviderInterface;
use EonX\EasyServerless\AppMetric\Client\AppMetricClient;
use EonX\EasyServerless\AppMetric\Client\AppMetricClientInterface;
use EonX\EasyServerless\Bundle\Enum\ConfigTag;
use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use EonX\EasyServerless\Health\Checker\SanityChecker;
use EonX\EasyServerless\Health\Controller\HealthCheckController;
use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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
        $this->registerHealth();

        if (\class_exists(PaginationInterface::class)) {
            $this->registerPagination();
        }

        $this->app->bind(SqsHandler::class, static function (Container $app): SqsHandler {
            $config = $app->make('config');
            $connectionName = $config->get('queue.default', 'sqs');
            $getQueueConfig = static fn (string $name, mixed $default): mixed => $config->get(
                \sprintf('queue.connections.%s.%s', $connectionName, $name),
                $default
            );

            return new SqsHandler(
                container: $app,
                connectionName: $connectionName,
                appMaxRetries: $getQueueConfig('max_retries', 3),
                timeoutThresholdMilliseconds: $getQueueConfig('timeout_threshold_ms', 1000),
                partialBatchFailure: $getQueueConfig('partial_batch_failure', false),
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
