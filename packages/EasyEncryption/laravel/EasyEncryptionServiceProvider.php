<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Laravel;

use EonX\EasyEncryption\AwsPkcs11Encryptor;
use EonX\EasyEncryption\Bundle\Enum\ConfigServiceId;
use EonX\EasyEncryption\Builder\AwsCloudHsmSdkOptionsBuilder;
use EonX\EasyEncryption\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Encryptors\StringEncryptor;
use EonX\EasyEncryption\Encryptors\StringEncryptorInterface;
use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\HashCalculators\AwsCloudHsmHashCalculator;
use EonX\EasyEncryption\HashCalculators\HashCalculatorInterface;
use EonX\EasyEncryption\Interfaces\AwsPkcs11EncryptorInterface;
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
        $this->registerAwsCloudHsmEncryptor();
    }

    private function registerAwsCloudHsmEncryptor(): void
    {
        if (\config('easy-encryption.aws_cloud_hsm_encryptor.enabled', true) === false) {
            return;
        }

        $this->app->singleton(
            AwsCloudHsmSdkOptionsBuilder::class,
            static fn (): AwsCloudHsmSdkOptionsBuilder => new AwsCloudHsmSdkOptionsBuilder(
                hsmCaCert: \config('easy-encryption.aws_pkcs11_hsm_ca_cert'),
                disableKeyAvailabilityCheck: \config('easy-encryption.aws_pkcs11_disable_key_availability_check'),
                hsmIpAddress: \config('easy-encryption.aws_pkcs11_hsm_ip_address'),
                cloudHsmClusterId: \config('easy-encryption.aws_pkcs11_cloud_hsm_cluster_id'),
                awsRegion: \config('easy-encryption.aws_pkcs11_aws_region'),
                serverClientCertFile: \config('easy-encryption.aws_pkcs11_server_client_cert_file'),
                serverClientKeyFile: \config('easy-encryption.aws_pkcs11_server_client_key_file'),
                cloudHsmSdkOptions: \config('easy-encryption.aws_pkcs11_cloud_hsm_sdk_options')
            )
        );

        $this->app->singleton(
            AwsCloudHsmSdkConfigurator::class,
            static fn (Container $app): AwsCloudHsmSdkConfigurator => new AwsCloudHsmSdkConfigurator(
                awsCloudHsmSdkOptionsBuilder: $app->make(AwsCloudHsmSdkOptionsBuilder::class),
                awsRoleArn: \config('easy-encryption.aws_pkcs11_aws_role_arn'),
                useCloudHsmConfigureTool: (bool)\config('easy-encryption.aws_pkcs11_use_cloud_hsm_configure_tool')
            )
        );

        $this->app->singleton(
            AwsPkcs11EncryptorInterface::class,
            static fn (Container $app): AwsPkcs11EncryptorInterface => new AwsPkcs11Encryptor(
                userPin: \config('easy-encryption.aws_pkcs11_user_pin'),
                awsCloudHsmSdkConfigurator: $app->make(AwsCloudHsmSdkConfigurator::class),
                aad: \config('easy-encryption.aws_pkcs11_aad'),
                defaultKeyName: \config('easy-encryption.default_key_name')
            )
        );

        $this->app->singleton(
            HashCalculatorInterface::class,
            static fn (Container $app): HashCalculatorInterface => new AwsCloudHsmHashCalculator(
                encryptor: $app->make(AwsPkcs11EncryptorInterface::class),
                signKeyName: \config('easy-encryption.default_key_name')
            )
        );

        $this->app->singleton(
            StringEncryptorInterface::class,
            static fn (Container $app): StringEncryptorInterface => new StringEncryptor(
                encryptor: $app->make(AwsPkcs11EncryptorInterface::class),
                encryptionKeyName: \config('easy-encryption.default_key_name'),
                maxChunkSize: \config('easy-encryption.max_chunk_size')
            )
        );
    }

    private function registerDefaultKeyResolvers(): void
    {
        if (\config('easy-encryption.use_default_key_resolvers', true) === false) {
            return;
        }

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
