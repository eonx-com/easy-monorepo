<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Providers;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EonX\EasyAsync\Bridge\BridgeConstantsInterface;
use EonX\EasyAsync\Bridge\Laravel\Queue\DoctrineManagersClearListener;
use EonX\EasyAsync\Bridge\Laravel\Queue\DoctrineManagersSanityCheckListener;
use EonX\EasyAsync\Bridge\Laravel\Queue\ShouldKillWorkerListener;
use EonX\EasyAsync\Doctrine\ManagersClearer;
use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use EonX\EasyAsync\Exceptions\InvalidImplementationException;
use EonX\EasyAsync\Factories\JobFactory;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\DataCleaner;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobLogPersister;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobPersister;
use EonX\EasyAsync\Interfaces\DataCleanerInterface;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\ImplementationsInterface;
use EonX\EasyAsync\Interfaces\JobFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Persisters\WithEventsJobPersister;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;
use EonX\EasyEventDispatcher\Interfaces\EventDispatcherInterface;
use EonX\EasyLogging\Bridge\BridgeConstantsInterface as EasyLoggingBridgeConstantsInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
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
            __DIR__ . '/../config/easy-async.php' => \base_path('config/easy-async.php'),
        ]);

        $this->app->make('events')
            ->listen(JobFailed::class, ShouldKillWorkerListener::class);

        if (\interface_exists(EntityManagerInterface::class)) {
            $this->app->make('events')
                ->listen(JobProcessing::class, DoctrineManagersSanityCheckListener::class);

            $this->app->make('events')
                ->listen(JobProcessing::class, DoctrineManagersClearListener::class);
        }
    }

    /**
     * @throws \EonX\EasyAsync\Exceptions\InvalidImplementationException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-async.php', 'easy-async');

        $this->registerAsyncLogger();
        $this->registerDoctrineQueueListeners();

        $simples = [
            DateTimeGeneratorInterface::class => DateTimeGenerator::class,
            JobFactoryInterface::class => JobFactory::class,
            JobLogFactoryInterface::class => JobLogFactory::class,
            'default_job_log_updater' => JobLogUpdater::class,
            JobLogUpdaterInterface::class => WithEventsJobLogUpdater::class,
        ];

        foreach ($simples as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        $this->app->singleton(
            JobPersisterInterface::class,
            static function (Container $app): JobPersisterInterface {
                return new WithEventsJobPersister(
                    $app->get('default_job_persister'),
                    $app->get(EventDispatcherInterface::class)
                );
            }
        );

        $this->app->singleton(
            JobLogUpdaterInterface::class,
            static function (Container $app): JobLogUpdaterInterface {
                return new WithEventsJobLogUpdater(
                    $app->get(EventDispatcherInterface::class),
                    $app->get('default_job_log_updater')
                );
            }
        );

        $implementation = \config('easy-async.implementation', ImplementationsInterface::IMPLEMENTATION_DOCTRINE);

        if ($implementation === ImplementationsInterface::IMPLEMENTATION_DOCTRINE) {
            $this->registerDoctrine();

            return;
        }

        throw new InvalidImplementationException(\sprintf('Implementation "%s" invalid', $implementation));
    }

    private function registerAsyncLogger(): void
    {
        $this->app->singleton(
            BridgeConstantsInterface::SERVICE_LOGGER,
            static function (Container $app): LoggerInterface {
                $loggerParams = \interface_exists(EasyLoggingBridgeConstantsInterface::class)
                    ? [EasyLoggingBridgeConstantsInterface::KEY_CHANNEL => BridgeConstantsInterface::LOG_CHANNEL]
                    : [];

                return $app->make(LoggerInterface::class, $loggerParams);
            }
        );
    }

    private function registerDoctrine(): void
    {
        $this->app->singleton(DataCleanerInterface::class, DataCleaner::class);
        $this->app->singleton(JobLogPersisterInterface::class, JobLogPersister::class);
        $this->app->singleton('default_job_persister', JobPersister::class);

        $this
            ->app
            ->when(JobLogPersister::class)
            ->needs('$table')
            ->give(\config('easy-async.job_logs_table', 'easy_async_job_logs'));

        $this
            ->app
            ->when(JobPersister::class)
            ->needs('$table')
            ->give(\config('easy-async.jobs_table', 'easy_async_jobs'));
    }

    private function registerDoctrineQueueListeners(): void
    {
        $this->app->singleton(ManagersSanityChecker::class, static function (Container $app): ManagersSanityChecker {
            return new ManagersSanityChecker(
                $app->make(ManagerRegistry::class),
                $app->make(BridgeConstantsInterface::SERVICE_LOGGER)
            );
        });

        $this->app->singleton(
            DoctrineManagersClearListener::class,
            static function (Container $app): DoctrineManagersClearListener {
                return new DoctrineManagersClearListener(
                    $app->make(ManagersClearer::class),
                    \config('easy-async.queue.managers_to_clear'),
                    $app->make(BridgeConstantsInterface::SERVICE_LOGGER)
                );
            }
        );

        $this->app->singleton(
            DoctrineManagersSanityCheckListener::class,
            static function (Container $app): DoctrineManagersSanityCheckListener {
                return new DoctrineManagersSanityCheckListener(
                    $app->make('cache'),
                    $app->make(ManagersSanityChecker::class),
                    \config('easy-async.queue.managers_to_check'),
                    $app->make(BridgeConstantsInterface::SERVICE_LOGGER)
                );
            }
        );
    }
}
