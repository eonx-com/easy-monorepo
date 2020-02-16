<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Providers;

use EonX\EasyCore\Bridge\Laravel\Listeners\DoctrineClearEmBeforeJobListener;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;

final class DoctrineClearEmBeforeJobServiceProvider extends ServiceProvider
{
    /**
     * Register listener for JobProcessing event.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->get('events')->listen(JobProcessing::class, DoctrineClearEmBeforeJobListener::class);
    }
}
