<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Laravel;

use EonX\EasyApiToken\Bundle\Enum\ConfigTag;
use EonX\EasyApiToken\Common\Decoder\DecoderInterface;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriver;
use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriverInterface;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactory;
use EonX\EasyApiToken\Common\Factory\ApiTokenDecoderFactoryInterface as DecoderFactoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyApiTokenServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->app->singleton(
            HashedApiKeyDriverInterface::class,
            static fn (): HashedApiKeyDriver => new HashedApiKeyDriver()
        );

        $this->app->singleton(
            DecoderInterface::class,
            static fn (
                Container $app,
            ): DecoderInterface => $app->make(DecoderFactoryInterface::class)->buildDefault()
        );

        $this->app->singleton(
            DecoderFactoryInterface::class,
            static fn (Container $app): DecoderFactoryInterface => new ApiTokenDecoderFactory(
                $app->tagged(ConfigTag::DecoderProvider->value),
                $app->make(HashedApiKeyDriverInterface::class)
            )
        );
    }
}
