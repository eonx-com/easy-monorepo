<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use EonX\EasyServerless\Laravel\Queue\SqsQueueHandler;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyServerlessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SqsQueueHandler::class, static function (Container $app): SqsQueueHandler {
            return new SqsQueueHandler(
                $app,
                $app->make('config')->get('queue.default', 'sqs'),
                $app->make('config')->get('queue.connections.sqs.partial_batch_failure', false),
            );
        });
    }
}
