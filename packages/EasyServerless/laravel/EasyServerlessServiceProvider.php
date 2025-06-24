<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Laravel;

use EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyServerlessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SqsHandler::class, static function (Container $app): SqsHandler {
            $config = $app->make('config');

            return new SqsHandler(
                $app,
                $config->get('queue.default', 'sqs'),
                $config->get('queue.connections.sqs.partial_batch_failure', false),
            );
        });
    }
}
