<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyAsync\Bundle\Enum\BundleParam;
use EonX\EasyAsync\Bundle\Enum\ConfigServiceId;
use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use EonX\EasyAsync\Doctrine\Clearer\ManagersClearer;
use EonX\EasyAsync\Laravel\Listeners\DoctrineManagersClearListener;
use EonX\EasyAsync\Laravel\Listeners\DoctrineManagersSanityCheckListener;
use EonX\EasyAsync\Laravel\Listeners\QueueWorkerStoppingListener;
use EonX\EasyAsync\Laravel\Listeners\ShouldKillWorkerListener;
use EonX\EasyAsync\Laravel\Listeners\WorkerStoppingListener;
use EonX\EasyErrorHandler\Bridge\Laravel\Provider\EasyErrorHandlerServiceProvider;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use EonX\EasyLogging\Bundle\Enum\BundleParam as EasyLoggingBundleParam;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class EasyAsyncServiceProvider extends ServiceProvider
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-async.php' => \base_path('config/easy-async.php'),
        ]);

        $events = $this->app->make('events');

        $events->listen(JobFailed::class, ShouldKillWorkerListener::class);
        $events->listen(WorkerStopping::class, QueueWorkerStoppingListener::class);

        if (\interface_exists(EntityManagerInterface::class)) {
            $events->listen(JobProcessing::class, DoctrineManagersSanityCheckListener::class);
            $events->listen(JobProcessing::class, DoctrineManagersClearListener::class);
        }

        if (\class_exists(EasyErrorHandlerServiceProvider::class)
            && \config('easy-async.easy_error_handler_worker_stopping_enabled', true)) {
            $events->listen(WorkerStopping::class, WorkerStoppingListener::class);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-async.php', 'easy-async');

        $this->clearDoctrineEmBeforeJob();
        $this->logQueueWorkerStopping();
        $this->registerAsyncLogger();
        $this->registerEasyErrorHandlerBridge();
        $this->registerQueueListeners();
        $this->restartQueueOnEmClose();
    }

    private function clearDoctrineEmBeforeJob(): void
    {
        if ((bool)\config('easy-async.clear_doctrine_em_before_job', false) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(JobProcessing::class, DoctrineManagersClearListener::class);
    }

    private function logQueueWorkerStopping(): void
    {
        if ((bool)\config('easy-async.log_queue_worker_stop', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(WorkerStopping::class, QueueWorkerStoppingListener::class);
    }

    private function registerAsyncLogger(): void
    {
        $this->app->singleton(
            ConfigServiceId::Logger->value,
            static function (Container $app): LoggerInterface {
                $loggerParams = \enum_exists(EasyLoggingBundleParam::class)
                    ? [EasyLoggingBundleParam::KeyChannel->value => BundleParam::LogChannel->value]
                    : [];

                return $app->make(LoggerInterface::class, $loggerParams);
            }
        );
    }

    private function registerEasyErrorHandlerBridge(): void
    {
        if (\class_exists(EasyErrorHandlerServiceProvider::class) === false
            || \config('easy-async.easy_error_handler_worker_stopping_enabled', true) === false) {
            return;
        }

        $this->app->singleton(
            WorkerStoppingListener::class,
            static fn (
                Container $app,
            ): WorkerStoppingListener => new WorkerStoppingListener($app->make(ErrorHandlerInterface::class))
        );
    }

    private function registerQueueListeners(): void
    {
        $this->app->singleton(
            ManagersSanityChecker::class,
            static fn (Container $app): ManagersSanityChecker => new ManagersSanityChecker(
                $app->make(ManagerRegistry::class),
                $app->make(ConfigServiceId::Logger->value)
            )
        );

        $this->app->singleton(
            DoctrineManagersClearListener::class,
            static fn (Container $app): DoctrineManagersClearListener => new DoctrineManagersClearListener(
                $app->make(ManagersClearer::class),
                \config('easy-async.queue.managers_to_clear'),
                $app->make(ConfigServiceId::Logger->value)
            )
        );

        $this->app->singleton(
            DoctrineManagersSanityCheckListener::class,
            static fn (Container $app): DoctrineManagersSanityCheckListener => new DoctrineManagersSanityCheckListener(
                $app->make('cache.store'),
                $app->make(ManagersSanityChecker::class),
                \config('easy-async.queue.managers_to_check'),
                $app->make(ConfigServiceId::Logger->value)
            )
        );

        $this->app->singleton(
            QueueWorkerStoppingListener::class,
            static fn (Container $app): QueueWorkerStoppingListener => new QueueWorkerStoppingListener(
                $app->make(ConfigServiceId::Logger->value)
            )
        );
    }

    private function restartQueueOnEmClose(): void
    {
        if ((bool)\config('easy-async.restart_queue_on_doctrine_em_close', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(JobExceptionOccurred::class, DoctrineManagersSanityCheckListener::class);
    }
}
