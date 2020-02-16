<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Providers;

use EonX\EasyCore\Bridge\Laravel\Listeners\QueueWorkerStoppingListener;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\ServiceProvider;

final class QueueWorkerStoppingServiceProvider extends ServiceProvider
{
    /**
     * Register listener for WorkerStopping event.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->get('events')->listen(WorkerStopping::class, QueueWorkerStoppingListener::class);
    }
}
