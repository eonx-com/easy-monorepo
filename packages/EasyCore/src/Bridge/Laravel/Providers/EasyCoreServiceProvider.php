<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Providers;

use EonX\EasyCore\Bridge\Laravel\Listeners\DoctrineClearEmBeforeJobListener;
use EonX\EasyCore\Bridge\Laravel\Listeners\DoctrineRestartQueueOnEmCloseListener;
use EonX\EasyCore\Bridge\Laravel\Listeners\QueueWorkerStoppingListener;
use EonX\EasyCore\Bridge\Laravel\Middleware\TrimStrings;
use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use EonX\EasyCore\Search\ElasticsearchSearchServiceFactory;
use EonX\EasyCore\Search\SearchServiceFactoryInterface;
use EonX\EasyCore\Search\SearchServiceInterface;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;

use function base_path;
use function config;

final class EasyCoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-core.php' => base_path('config/easy-core.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-core.php', 'easy-core');

        $this->clearDoctrineEmBeforeJob();
        $this->logQueueWorkerStopping();
        $this->restartQueueOnEmClose();
        $this->search();
        $this->trimStrings();
    }

    private function trimStrings(): void
    {
        if ((bool)config('easy-core.trim_strings.enabled', true) === false) {
            return;
        }

        $this->app->singleton(StringsTrimmerInterface::class, RecursiveStringsTrimmer::class);
        $this->app->singleton(TrimStrings::class, function (): TrimStrings {
            return new TrimStrings(
                $this->app->get(StringsTrimmerInterface::class),
                config('easy-core.trim_strings.except', [])
            );
        });
    }

    private function clearDoctrineEmBeforeJob(): void
    {
        if ((bool)config('easy-core.clear_doctrine_em_before_job', false) === false) {
            return;
        }

        $this->app->get('events')->listen(JobProcessing::class, DoctrineClearEmBeforeJobListener::class);
    }

    private function logQueueWorkerStopping(): void
    {
        if ((bool)config('easy-core.log_queue_worker_stop', true) === false) {
            return;
        }

        $this->app->get('events')->listen(WorkerStopping::class, QueueWorkerStoppingListener::class);
    }

    private function restartQueueOnEmClose(): void
    {
        if ((bool)config('easy-core.restart_queue_on_doctrine_em_close', true) === false) {
            return;
        }

        $this->app->get('events')->listen(JobExceptionOccurred::class, DoctrineRestartQueueOnEmCloseListener::class);
    }

    private function search(): void
    {
        if ((bool)config('easy-core.search.enabled', false) === false) {
            return;
        }

        $this->app->singleton(SearchServiceFactoryInterface::class, function (): SearchServiceFactoryInterface {
            return new ElasticsearchSearchServiceFactory(config('easy-core.search.elasticsearch_host'));
        });

        $this->app->singleton(SearchServiceInterface::class, function (): SearchServiceInterface {
            return $this->app->make(SearchServiceFactoryInterface::class)->create();
        });
    }
}
