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

        $container
            ->registerForAutoconfiguration(EncryptionKeyResolverInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_ENCRYPTION_KEY_RESOLVER);

        if ($config['use_default_key_resolvers'] ?? true) {
            $loader->load('default_key_resolvers.php');
        }
    }
}
