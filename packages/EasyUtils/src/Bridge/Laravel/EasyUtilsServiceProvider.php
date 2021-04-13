<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Laravel;

use EonX\EasyUtils\Interfaces\MathInterface;
use EonX\EasyUtils\Math;
use Illuminate\Support\ServiceProvider;

final class EasyUtilsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-utils.php' => \base_path('config/easy-utils.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-utils.php', 'easy-utils');

        $this->app->singleton(MathInterface::class, static function (): MathInterface {
            return new Math(
                \config('easy-utils.round-precision'),
                \config('easy-utils.round-mode'),
                \config('easy-utils.scale'),
                \config('easy-utils.format-decimal-separator'),
                \config('easy-utils.format-thousands-separator')
            );
        });
    }
}
