<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Providers;

use EonX\EasyAsync\Bridge\Laravel\Events\EventDispatcher;
use EonX\EasyAsync\Factories\JobFactory;
use EonX\EasyAsync\Factories\JobLogFactory;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Generators\RamseyUuidGenerator;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\DataCleaner;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobLogPersister;
use EonX\EasyAsync\Implementations\Doctrine\DBAL\JobPersister;
use EonX\EasyAsync\Interfaces\DataCleanerInterface;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EventDispatcherInterface;
use EonX\EasyAsync\Interfaces\JobFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogFactoryInterface;
use EonX\EasyAsync\Interfaces\JobLogPersisterInterface;
use EonX\EasyAsync\Interfaces\JobLogUpdaterInterface;
use EonX\EasyAsync\Interfaces\JobPersisterInterface;
use EonX\EasyAsync\Interfaces\UuidGeneratorInterface;
use EonX\EasyAsync\Persisters\WithEventsJobPersister;
use EonX\EasyAsync\Updaters\JobLogUpdater;
use EonX\EasyAsync\Updaters\WithEventsJobLogUpdater;
use Illuminate\Support\ServiceProvider;

final class EasyAsyncServiceProvider extends ServiceProvider
{
    /**
     * Publish configuration file.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-async.php' => \base_path('config/easy-async.php')
        ]);
    }

    /**
     * Register easy-async services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-async.php', 'easy-async');

        $simples = [
            DateTimeGeneratorInterface::class => DateTimeGenerator::class,
            EventDispatcherInterface::class => EventDispatcher::class,
            JobFactoryInterface::class => JobFactory::class,
            JobLogFactoryInterface::class => JobLogFactory::class,
            UuidGeneratorInterface::class => RamseyUuidGenerator::class,
            'default_job_log_updater' => JobLogUpdater::class,
            JobPersisterInterface::class => WithEventsJobPersister::class
        ];

        foreach ($simples as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }

        $this->app->singleton(JobLogUpdaterInterface::class, function (): JobLogUpdaterInterface {
            return new WithEventsJobLogUpdater(
                $this->app->get(EventDispatcherInterface::class),
                $this->app->get('default_job_log_updater')
            );
        });

        // Inject decorated job persister
        $this
            ->app
            ->when(WithEventsJobPersister::class)
            ->needs('$decorated')
            ->give(function (): JobPersisterInterface {
                return $this->app->get('default_job_persister');
            });

        $implementation = \config('easy-async.implementation', 'doctrine');

        if ($implementation === 'doctrine') {
            $this->registerDoctrine();

            return;
        }
    }

    /**
     * Register doctrine implementation.
     *
     * @return void
     */
    private function registerDoctrine(): void
    {
        $this->app->singleton(DataCleanerInterface::class, DataCleaner::class);
        $this->app->singleton(JobLogPersisterInterface::class, JobLogPersister::class);
        $this->app->singleton('default_job_persister', JobPersister::class);

        $this->app->when(JobLogPersister::class)->needs('$table')->give(\config('easy-async.job_logs_table'));
        $this->app->when(JobPersister::class)->needs('$table')->give(\config('easy-async.jobs_table'));
    }
}
