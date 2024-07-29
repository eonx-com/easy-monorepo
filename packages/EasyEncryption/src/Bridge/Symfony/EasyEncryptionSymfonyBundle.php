<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge\Symfony;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class EasyEncryptionSymfonyBundle extends AbstractBundle
{
    private const AWS_PKCS11_CONFIGS_TO_PARAMS = [
        'aad' => BridgeConstantsInterface::PARAM_AWS_PKCS11_AAD,
        'aws_cloud_hsm_sdk_options' => BridgeConstantsInterface::PARAM_AWS_PKCS11_CLOUD_HSM_SDK_OPTIONS,
        'aws_region' => BridgeConstantsInterface::PARAM_AWS_PKCS11_AWS_REGION,
        'aws_role_arn' => BridgeConstantsInterface::PARAM_AWS_PKCS11_AWS_ROLE_ARN,
        'cloud_hsm_cluster_id' => BridgeConstantsInterface::PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID,
        'disable_key_availability_check' => BridgeConstantsInterface::PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK,
        'hsm_ca_cert' => BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_CA_CERT,
        'hsm_ip_address' => BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_IP_ADDRESS,
        'server_client_cert_file' => BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE,
        'server_client_key_file' => BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE,
        'use_aws_cloud_hsm_configure_tool' => BridgeConstantsInterface::PARAM_AWS_PKCS11_USE_CLOUD_HSM_CONFIGURE_TOOL,
        'user_pin' => BridgeConstantsInterface::PARAM_AWS_PKCS11_USER_PIN,
    ];

    private const CONFIGS_TO_PARAMS = [
        'default_encryption_key' => BridgeConstantsInterface::PARAM_DEFAULT_ENCRYPTION_KEY,
        'default_key_name' => BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME,
        'default_salt' => BridgeConstantsInterface::PARAM_DEFAULT_SALT,
        'fully_encrypted_messages' => BridgeConstantsInterface::PARAM_FULLY_ENCRYPTED_MESSAGES,
        'max_chunk_size' => BridgeConstantsInterface::PARAM_MAX_CHUNK_SIZE,
    ];

    protected string $extensionAlias = 'easy_encryption';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        foreach (self::CONFIGS_TO_PARAMS as $configName => $param) {
            $container
                ->parameters()
                ->set($param, $config[$configName]);
        }

        foreach (self::AWS_PKCS11_CONFIGS_TO_PARAMS as $configName => $param) {
            $container
                ->parameters()
                ->set($param, $config['aws_pkcs11_encryptor'][$configName]);
        }

        $builder
            ->registerForAutoconfiguration(EncryptionKeyResolverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ENCRYPTION_KEY_RESOLVER);

        if ($config['use_default_key_resolvers'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/default_key_resolvers.php');
        }

        if ($config['aws_pkcs11_encryptor']['enabled'] ?? false) {
            $container->import(__DIR__ . '/Resources/config/aws_pkcs11_encryptor.php');
        }

        if (\class_exists(SerializerInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/encryptable_messenger.php');
        }
    }
}
