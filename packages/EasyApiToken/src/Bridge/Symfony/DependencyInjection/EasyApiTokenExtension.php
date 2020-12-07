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

        $container->registerForAutoconfiguration(ApiTokenDecoderProviderInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_DECODER_PROVIDER);

        // Resolve config
        $decoders = [];
        $defaultFactories = null;
        $defaultDecoder = null;

        foreach ($configs as $config) {
            if (isset($config['decoders'])) {
                $decoders = $config['decoders'];
            }

            if (isset($config['default_decoder'])) {
                $defaultDecoder = $config['default_decoder'];
            }

            if (isset($config['default_factories'])) {
                $defaultFactories = $config['default_factories'];
            }
        }

        if (empty($decoders) === false) {
            @\trigger_error(\sprintf(
                'Defining ApiTokenDecoders using a config file is deprecated since 2.4 and will be removed in 3.0.
                Use %s instead.',
                ApiTokenDecoderProviderInterface::class
            ), \E_USER_NOTICE);

            $container->setParameter(BridgeConstantsInterface::PARAM_DECODERS, $decoders);
            $container->setParameter(BridgeConstantsInterface::PARAM_DEFAULT_FACTORIES, $defaultFactories);
            $container->setParameter(BridgeConstantsInterface::PARAM_DEFAULT_DECODER, $defaultDecoder);

            $loader->load('from_config_provider.php');
        }
    }
}
