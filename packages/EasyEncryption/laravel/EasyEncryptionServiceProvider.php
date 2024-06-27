<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Laravel;

use EonX\EasyEncryption\Bundle\Enum\ConfigServiceId;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Factory\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Common\Provider\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EasyEncryptionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/easy-encryption.php' => \base_path('config/easy-encryption.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/easy-encryption.php', 'easy-encryption');

        $this->registerFactory();
        $this->registerProvider();
        $this->registerEncryptor();
        $this->registerDefaultKeyResolvers();
    }

    private function registerDefaultKeyResolvers(): void
    {
        if (\config('easy-encryption.use_default_key_resolvers', true)) {
            $this->app->singleton(
                ConfigServiceId::DefaultKeyResolver->value,
                static fn (): SimpleEncryptionKeyResolver => new SimpleEncryptionKeyResolver(
                    \config('easy-encryption.default_key_name'),
                    \config('easy-encryption.default_encryption_key'),
                    \config('easy-encryption.default_salt')
                )
            );

            $this->app->tag(
                ConfigServiceId::DefaultKeyResolver->value,
                [ConfigTag::EncryptionKeyResolver->value]
            );
        }
    }

    private function registerEncryptor(): void
    {
        $this->app->singleton(
            EncryptorInterface::class,
            static fn (Container $app): EncryptorInterface => new Encryptor(
                $app->make(EncryptionKeyFactoryInterface::class),
                $app->make(EncryptionKeyProviderInterface::class),
                \config('easy-encryption.default_key_name')
            )
        );
    }

    private function registerFactory(): void
    {
        $this->app->singleton(
            EncryptionKeyFactoryInterface::class,
            static fn (): EncryptionKeyFactoryInterface => new DefaultEncryptionKeyFactory()
        );
    }

    private function registerProvider(): void
    {
        $this->app->singleton(
            EncryptionKeyProviderInterface::class,
            static fn (Container $app): EncryptionKeyProviderInterface => new DefaultEncryptionKeyProvider(
                $app->make(EncryptionKeyFactoryInterface::class),
                $app->tagged(ConfigTag::EncryptionKeyResolver->value)
            )
        );
    }
}
