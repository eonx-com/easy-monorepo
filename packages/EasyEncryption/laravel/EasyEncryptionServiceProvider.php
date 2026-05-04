<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Laravel;

use EonX\EasyEncryption\AwsCloudHsm\Builder\AwsCloudHsmSdkOptionsBuilder;
use EonX\EasyEncryption\AwsCloudHsm\Configurator\AwsCloudHsmSdkConfigurator;
use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptor;
use EonX\EasyEncryption\AwsCloudHsm\Encryptor\AwsCloudHsmEncryptorInterface;
use EonX\EasyEncryption\AwsCloudHsm\HashCalculator\AwsCloudHsmHashCalculator;
use EonX\EasyEncryption\Bundle\Enum\ConfigServiceId;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Common\Encryptor\Encryptor;
use EonX\EasyEncryption\Common\Encryptor\EncryptorInterface;
use EonX\EasyEncryption\Common\Factory\DefaultEncryptionKeyFactory;
use EonX\EasyEncryption\Common\Factory\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Common\Provider\DefaultEncryptionKeyProvider;
use EonX\EasyEncryption\Common\Provider\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Common\Resolver\SimpleEncryptionKeyResolver;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptor;
use EonX\EasyEncryption\Encryptable\Encryptor\StringEncryptorInterface;
use EonX\EasyEncryption\Encryptable\HashCalculator\HashCalculatorInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

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

        $disableKeyAvailabilityCheck
            = \config('easy-encryption.aws_cloud_hsm_encryptor.disable_key_availability_check');

        $this->app->singleton(
            AwsCloudHsmSdkOptionsBuilder::class,
            static fn (): AwsCloudHsmSdkOptionsBuilder => new AwsCloudHsmSdkOptionsBuilder(
                caCertFile: \config('easy-encryption.aws_cloud_hsm_encryptor.ca_cert_file'),
                disableKeyAvailabilityCheck: $disableKeyAvailabilityCheck,
                ipAddress: \config('easy-encryption.aws_cloud_hsm_encryptor.ip_address'),
                clusterId: \config('easy-encryption.aws_cloud_hsm_encryptor.cluster_id'),
                region: \config('easy-encryption.aws_cloud_hsm_encryptor.region'),
                serverClientCertFile: \config('easy-encryption.aws_cloud_hsm_encryptor.server_client_cert_file'),
                serverClientKeyFile: \config('easy-encryption.aws_cloud_hsm_encryptor.server_client_key_file'),
                sdkOptions: \config('easy-encryption.aws_cloud_hsm_encryptor.sdk_options')
            )
        );

        $useConfigureTool
            = (bool)\config('easy-encryption.aws_cloud_hsm_encryptor.use_aws_cloud_hsm_configure_tool');

        $this->app->singleton(
            AwsCloudHsmSdkConfigurator::class,
            static fn (Container $app): AwsCloudHsmSdkConfigurator => new AwsCloudHsmSdkConfigurator(
                awsCloudHsmSdkOptionsBuilder: $app->make(AwsCloudHsmSdkOptionsBuilder::class),
                roleArn: \config('easy-encryption.aws_cloud_hsm_encryptor.role_arn'),
                useConfigureTool: $useConfigureTool
            )
        );

        $this->app->singleton(
            AwsCloudHsmEncryptorInterface::class,
            static fn (Container $app): AwsCloudHsmEncryptorInterface => new AwsCloudHsmEncryptor(
                userPin: \config('easy-encryption.aws_cloud_hsm_encryptor.user_pin'),
                awsCloudHsmSdkConfigurator: $app->make(AwsCloudHsmSdkConfigurator::class),
                aad: \config('easy-encryption.aws_cloud_hsm_encryptor.aad'),
                defaultKeyName: \config('easy-encryption.default_key_name'),
                logger: $app->make(LoggerInterface::class)
            )
        );

        $this->app->singleton(
            HashCalculatorInterface::class,
            static fn (Container $app): HashCalculatorInterface => new AwsCloudHsmHashCalculator(
                encryptor: $app->make(AwsCloudHsmEncryptorInterface::class),
                signKeyName: \config('easy-encryption.default_key_name')
            )
        );

        $this->app->singleton(
            StringEncryptorInterface::class,
            static fn (Container $app): StringEncryptorInterface => new StringEncryptor(
                encryptor: $app->make(AwsCloudHsmEncryptorInterface::class),
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
