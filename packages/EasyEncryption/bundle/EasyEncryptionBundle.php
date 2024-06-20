<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle;

use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Common\Resolver\EncryptionKeyResolverInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyEncryptionBundle extends AbstractBundle
{
    private const AWS_CLOUD_HSM_CONFIGS_TO_PARAMS = [
        'aad' => ConfigParam::AwsCloudHsmAad,
        'ca_cert_file' => ConfigParam::AwsCloudHsmCaCertFile,
        'cluster_id' => ConfigParam::AwsCloudHsmClusterId,
        'disable_key_availability_check' => ConfigParam::AwsCloudHsmDisableKeyAvailabilityCheck,
        'ip_address' => ConfigParam::AwsCloudHsmIpAddress,
        'region' => ConfigParam::AwsCloudHsmRegion,
        'role_arn' => ConfigParam::AwsCloudHsmRoleArn,
        'sdk_options' => ConfigParam::AwsCloudHsmSdkOptions,
        'server_client_cert_file' => ConfigParam::AwsCloudHsmServerClientCertFile,
        'server_client_key_file' => ConfigParam::AwsCloudHsmServerClientKeyFile,
        'use_aws_cloud_hsm_configure_tool' => ConfigParam::AwsCloudHsmUseConfigureTool,
        'user_pin' => ConfigParam::AwsCloudHsmUserPin,
    ];

    private const CONFIGS_TO_PARAMS = [
        'default_encryption_key' => ConfigParam::DefaultEncryptionKey,
        'default_key_name' => ConfigParam::DefaultKeyName,
        'default_salt' => ConfigParam::DefaultSalt,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/config/services.php');

        foreach (self::CONFIGS_TO_PARAMS as $configName => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$configName]);
        }

        foreach (self::AWS_CLOUD_HSM_CONFIGS_TO_PARAMS as $configName => $param) {
            $container
                ->parameters()
                ->set($param->value, $config['aws_cloud_hsm_encryptor'][$configName]);
        }

        $builder
            ->registerForAutoconfiguration(EncryptionKeyResolverInterface::class)
            ->addTag(ConfigTag::EncryptionKeyResolver->value);

        if ($config['use_default_key_resolvers'] ?? true) {
            $container->import(__DIR__ . '/config/default_key_resolvers.php');
        }

        if ($config['aws_cloud_hsm_encryptor']['enabled'] ?? false) {
            $container->import(__DIR__ . '/config/aws_cloud_hsm_encryptor.php');
        }
    }
}
