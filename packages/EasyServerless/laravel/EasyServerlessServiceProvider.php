<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

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

        $this->registerHealth();

        $this->app->bind(SqsHandler::class, static function (Container $app): SqsHandler {
            $config = $app->make('config');

            return new SqsHandler(
                $app,
                $config->get('queue.default', 'sqs'),
                $config->get('queue.connections.sqs.partial_batch_failure', false),
            );
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
}
