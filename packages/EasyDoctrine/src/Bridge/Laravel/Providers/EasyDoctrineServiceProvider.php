<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Laravel\Providers;

use EonX\EasyDoctrine\Bridge\EasyErrorHandler\TransactionalExceptionListener;
use EonX\EasyDoctrine\Events\TransactionalExceptionEvent;
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
        if ((bool)\config('easy-doctrine.easy_error_handler_enabled', true) === false) {
            return;
        }

        $this->app->get('events')
            ->listen(TransactionalExceptionEvent::class, [TransactionalExceptionListener::class, '__invoke']);
    }
}
