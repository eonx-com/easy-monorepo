<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Bridge\Symfony\DependencyInjection;

use EonX\EasyEncryption\Bridge\BridgeConstantsInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyEncryptionExtension extends Extension
{
    /**
     * @var string[]
     */
    private const CONFIGS_TO_PARAMS = [
        'default_key_name' => BridgeConstantsInterface::PARAM_DEFAULT_KEY_NAME,
        'default_encryption_key' => BridgeConstantsInterface::PARAM_DEFAULT_ENCRYPTION_KEY,
        'default_salt' => BridgeConstantsInterface::PARAM_DEFAULT_SALT,
    ];

    /**
     * @var string[]
     */
    private const AWS_PKCS11_CONFIGS_TO_PARAMS = [
        'user_pin' => BridgeConstantsInterface::PARAM_AWS_PKCS11_USER_PIN,
        'hsm_ca_cert' => BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_CA_CERT,
        'disable_key_availability_check' => BridgeConstantsInterface::PARAM_AWS_PKCS11_DISABLE_KEY_AVAILABILITY_CHECK,
        'hsm_ip_address' => BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_IP_ADDRESS,
        'cloud_hsm_cluster_id' => BridgeConstantsInterface::PARAM_AWS_PKCS11_CLOUD_HSM_CLUSTER_ID,
        'aws_region' => BridgeConstantsInterface::PARAM_AWS_PKCS11_AWS_REGION,
        'aad' => BridgeConstantsInterface::PARAM_AWS_PKCS11_AAD,
        'server_client_cert_file' => BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_CERT_FILE,
        'server_client_key_file' => BridgeConstantsInterface::PARAM_AWS_PKCS11_SERVER_CLIENT_KEY_FILE,
        'aws_cloud_hsm_sdk_options' => BridgeConstantsInterface::PARAM_AWS_PKCS11_HSM_SDK_OPTIONS,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));

        $loader->load('services.php');

        foreach (self::CONFIGS_TO_PARAMS as $configName => $param) {
            $container->setParameter($param, $config[$configName]);
        }

        foreach (self::AWS_PKCS11_CONFIGS_TO_PARAMS as $configName => $param) {
            $container->setParameter($param, $config['aws_pkcs11_encryptor'][$configName]);
        }

        $container
            ->registerForAutoconfiguration(EncryptionKeyResolverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ENCRYPTION_KEY_RESOLVER);

        if ($config['use_default_key_resolvers'] ?? true) {
            $loader->load('default_key_resolvers.php');
        }

        if ($config['aws_pkcs11_encryptor']['enabled'] ?? false) {
            $loader->load('aws_pkcs11_encryptor.php');
        }
    }
}
