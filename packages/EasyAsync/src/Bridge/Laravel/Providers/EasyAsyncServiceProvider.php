<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Providers;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Bridge\Laravel\Queue\DoctrineManagersSanityCheckListener;
use EonX\EasyAsync\Bridge\Laravel\Queue\ShouldKillWorkerListener;
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
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

final class EasyAsyncServiceProvider extends ServiceProvider
{
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
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-async.php', 'easy-async');

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
}
