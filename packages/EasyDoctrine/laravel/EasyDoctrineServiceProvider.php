<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Laravel;

use EonX\EasyDoctrine\EasyErrorHandler\Listener\WrapInTransactionExceptionListener;
use EonX\EasyDoctrine\EntityEvent\Event\WrapInTransactionExceptionEvent;
use Illuminate\Support\ServiceProvider;

final class EasyDoctrineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-doctrine.php' => \base_path('config/easy-doctrine.php'),
        ]);
    }

    public function register(): void
    {
        if ((bool)\config('easy-doctrine.easy_error_handler.enabled', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(
                WrapInTransactionExceptionEvent::class,
                [WrapInTransactionExceptionListener::class, '__invoke']
            );
    }
}
