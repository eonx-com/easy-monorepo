<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Bridge\Symfony\DependencyInjection;

use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
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

        $definition = $container->getDefinition(EasyApiTokenDecoderFactoryInterface::class);
        $definition->replaceArgument(0, $configs[0]['decoders'] ?? []);
        $definition->replaceArgument(1, $configs[0]['default_factories'] ?? null);
    }
}
