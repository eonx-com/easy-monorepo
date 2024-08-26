<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bundle;

use EonX\EasyEncryption\Bundle\CompilerPass\AwsCloudHsmCompilerPass;
use EonX\EasyEncryption\Bundle\Enum\ConfigParam;
use EonX\EasyEncryption\Bundle\Enum\ConfigTag;
use EonX\EasyEncryption\Common\Resolver\EncryptionKeyResolverInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyEncryptionBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AwsCloudHsmCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder
            ->registerForAutoconfiguration(EncryptionKeyResolverInterface::class)
            ->addTag(ConfigTag::EncryptionKeyResolver->value);

        $container
            ->parameters()
            ->set(ConfigParam::DefaultEncryptionKey->value, $config['default_encryption_key'])
            ->set(ConfigParam::DefaultKeyName->value, $config['default_key_name'])
            ->set(ConfigParam::DefaultSalt->value, $config['default_salt'])
            ->set(ConfigParam::FullyEncryptedMessages->value, $config['fully_encrypted_messages'])
            ->set(ConfigParam::MaxChunkSize->value, $config['max_chunk_size']);

        $container->import('config/services.php');

        if ($config['use_default_key_resolvers']) {
            $container->import('config/default_key_resolvers.php');
        }

        if (\class_exists(MessengerPass::class, false)) {
            $container->import('config/encryptable_messenger.php');
        }

        $this->registerAwsCloudHsmConfiguration($config, $container, $builder);
    }

    private function registerAwsCloudHsmConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_cloud_hsm_encryptor'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AwsCloudHsmAad->value, $config['aad'])
            ->set(ConfigParam::AwsCloudHsmCaCertFile->value, $config['ca_cert_file'])
            ->set(ConfigParam::AwsCloudHsmClusterId->value, $config['cluster_id'])
            ->set(ConfigParam::AwsCloudHsmDisableKeyAvailabilityCheck->value, $config['disable_key_availability_check'])
            ->set(ConfigParam::AwsCloudHsmEnabled->value, $config['enabled'])
            ->set(ConfigParam::AwsCloudHsmIpAddress->value, $config['ip_address'])
            ->set(ConfigParam::AwsCloudHsmRegion->value, $config['region'])
            ->set(ConfigParam::AwsCloudHsmRoleArn->value, $config['role_arn'])
            ->set(ConfigParam::AwsCloudHsmSdkOptions->value, $config['sdk_options'])
            ->set(ConfigParam::AwsCloudHsmServerClientCertFile->value, $config['server_client_cert_file'])
            ->set(ConfigParam::AwsCloudHsmServerClientKeyFile->value, $config['server_client_key_file'])
            ->set(ConfigParam::AwsCloudHsmUseConfigureTool->value, $config['use_aws_cloud_hsm_configure_tool'])
            ->set(ConfigParam::AwsCloudHsmSignKeyName->value, $config['sign_key_name'])
            ->set(ConfigParam::AwsCloudHsmUserPin->value, $config['user_pin']);
        $container->import('config/aws_cloud_hsm_encryptor.php');
    }
}
