<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiToken\Bridge\BridgeConstantsInterface;
use EonX\EasyApiToken\Interfaces\ApiTokenDecoderProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyApiTokenExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(ApiTokenDecoderProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECODER_PROVIDER);

        // Resolve config
        $decoders = [];
        $defaultFactories = null;

        foreach ($configs as $config) {
            if (isset($config['decoders'])) {
                $decoders = $config['decoders'];
            }

            $defaultFactories = $config['default_factories'] ?? null;
        }

        if (empty($decoders) === false) {
            $container->setParameter(BridgeConstantsInterface::PARAM_DECODERS, $decoders);
            $container->setParameter(BridgeConstantsInterface::PARAM_DEFAULT_FACTORIES, $defaultFactories);

            $loader->load('from_config_provider.php');
        }
    }
}
