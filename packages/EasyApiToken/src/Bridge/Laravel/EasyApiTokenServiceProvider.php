<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Laravel;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Factories\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\ApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Tokens\HashedApiKeyDriver;
use Illuminate\Contracts\Container\Container;
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

        $this->app->singleton(
            HashedApiKeyDriverInterface::class,
            static fn (): HashedApiKeyDriver => new HashedApiKeyDriver()
        );

        $this->app->singleton(
            ApiTokenDecoderInterface::class,
            static fn (
                Container $app,
            ): ApiTokenDecoderInterface => $app->make(DecoderFactoryInterface::class)->buildDefault()
        );

        $this->app->singleton(
            DecoderFactoryInterface::class,
            static fn (Container $app): DecoderFactoryInterface => new ApiTokenDecoderFactory(
                $app->tagged(BridgeConstantsInterface::TAG_DECODER_PROVIDER),
                $app->make(HashedApiKeyDriverInterface::class)
            )
        );
    }
}
