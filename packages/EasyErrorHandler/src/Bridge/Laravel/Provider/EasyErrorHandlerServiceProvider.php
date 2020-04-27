<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Provider;

use Illuminate\Support\ServiceProvider;

final class EasyErrorHandlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/easy-error-handler.php' => \base_path('config/easy-error-hanlder.php'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-error-handler.php', 'easy-error-handler');
    }
}
