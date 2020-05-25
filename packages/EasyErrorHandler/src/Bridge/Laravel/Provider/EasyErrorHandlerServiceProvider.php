<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Laravel\Provider;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\ServiceProvider;

final class EasyErrorHandlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../translations', 'easy-error-handler');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/easy-error-handler.php', 'easy-error-handler');

        $this->app->singleton(Translator::class, function () {
            return $this->app->make('translator');
        });
    }
}
