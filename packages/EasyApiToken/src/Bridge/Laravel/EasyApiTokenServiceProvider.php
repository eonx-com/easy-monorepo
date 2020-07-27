<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Laravel;

use EonX\EasyApiToken\Factories\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use Illuminate\Support\ServiceProvider;

final class EasyApiTokenServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-api-token.php' => \base_path('config/easy-api-token.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-api-token.php', 'easy-api-token');

        $this->app->singleton(DecoderFactoryInterface::class, function (): DecoderFactoryInterface {
            return new ApiTokenDecoderFactory(
                \config('easy-api-token.decoders', []),
                \config('easy-api-token.factories', null)
            );
        });
    }
}
