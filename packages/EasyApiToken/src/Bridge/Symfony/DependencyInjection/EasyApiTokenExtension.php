<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class EasyApiTokenExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param mixed[] $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        (new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config')))->load('services.xml');

        $configSource = isset($configs[1]) ? $configs[1] : $configs[0];

        \var_dump($configSource);

        $definition = $container->getDefinition(EasyApiTokenDecoderFactoryInterface::class);
        $definition->replaceArgument(0, $configSource['decoders'] ?? []);
        $definition->replaceArgument(1, $configSource['default_factories'] ?? null);
    }
}
