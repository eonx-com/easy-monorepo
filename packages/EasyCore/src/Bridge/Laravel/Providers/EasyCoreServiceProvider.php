<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Providers;

use EonX\EasyAsync\Bridge\Laravel\Queue\DoctrineManagersClearListener;
use EonX\EasyAsync\Bridge\Laravel\Queue\DoctrineManagersSanityCheckListener;
use EonX\EasyAsync\Bridge\Laravel\Queue\QueueWorkerStoppingListener;
use EonX\EasyCore\Bridge\Laravel\Middleware\TrimStrings;
use EonX\EasyCore\Helpers\RecursiveStringsTrimmer;
use EonX\EasyCore\Helpers\StringsTrimmerInterface;
use EonX\EasyCore\Search\ElasticsearchSearchServiceFactory;
use EonX\EasyCore\Search\SearchServiceFactoryInterface;
use EonX\EasyCore\Search\SearchServiceInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;

final class EasyCoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-core.php' => \base_path('config/easy-core.php'),
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

    private function clearDoctrineEmBeforeJob(): void
    {
        if ((bool)\config('easy-core.clear_doctrine_em_before_job', false) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(JobProcessing::class, DoctrineManagersClearListener::class);
    }

    private function logQueueWorkerStopping(): void
    {
        if ((bool)\config('easy-core.log_queue_worker_stop', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(WorkerStopping::class, QueueWorkerStoppingListener::class);
    }

    private function restartQueueOnEmClose(): void
    {
        if ((bool)\config('easy-core.restart_queue_on_doctrine_em_close', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(JobExceptionOccurred::class, DoctrineManagersSanityCheckListener::class);
    }

    private function search(): void
    {
        if ((bool)\config('easy-core.search.enabled', false) === false) {
            return;
        }

        $this->app->singleton(SearchServiceFactoryInterface::class, static function (): SearchServiceFactoryInterface {
            return new ElasticsearchSearchServiceFactory(\config('easy-core.search.elasticsearch_host'));
        });

        $this->app->singleton(
            SearchServiceInterface::class,
            static function (Container $app): SearchServiceInterface {
                return $app->make(SearchServiceFactoryInterface::class)->create();
            },
        );
    }

    private function trimStrings(): void
    {
        if ((bool)\config('easy-core.trim_strings.enabled', false) === false) {
            return;
        }

        /** @var \Laravel\Lumen\Application $app */
        $app = $this->app;
        $app->singleton(StringsTrimmerInterface::class, RecursiveStringsTrimmer::class);
        $app->singleton(TrimStrings::class, static function (Container $app): TrimStrings {
            return new TrimStrings(
                $app->get(StringsTrimmerInterface::class),
                \config('easy-core.trim_strings.except', []),
            );
        });
        $app->middleware([TrimStrings::class]);
    }
}
