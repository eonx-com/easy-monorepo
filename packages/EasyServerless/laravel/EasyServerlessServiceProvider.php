<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use EonX\EasyServerless\Laravel\Queues\SqsQueueHandler;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyServerlessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SqsQueueHandler::class, static function (Container $app): SqsQueueHandler {
            $config = $app->make('config');

            return new SqsQueueHandler(
                $app,
                $config->get('queue.default', 'sqs'),
                $config->get('queue.connections.sqs.partial_batch_failure', false),
            );
        });
    }
}
