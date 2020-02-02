<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Factories\EasyApiTokenDecoderFactory;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextDataResolverInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class EasySecurityExtension extends Extension
{
    /**
     * Load config and services.
     *
     * @param mixed[] $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return void
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Register context data resolvers for auto tagging
        $container->registerForAutoconfiguration(ContextDataResolverInterface::class)->addTag(TagsInterface::TAG_CONTEXT_DATA_RESOLVER);

        // Define dependencies for context resolver
        $def = $container->getDefinition(ContextResolverInterface::class);
        $tokenDecoderServiceId = 'easy_security.token_decoder';

        // Use EasyApiToken package directly to build decoder
        $tokenDecoderDef = (new Definition())
            ->setFactory([\sprintf('@%s', EasyApiTokenDecoderFactoryInterface::class), 'build'])
            ->setArguments([$config['token_decoder']]);

        $container->setDefinition($tokenDecoderServiceId, $tokenDecoderDef);

        $def->setArgument(3, new Reference($tokenDecoderServiceId));
        $def->setArgument(4, new TaggedIteratorArgument(TagsInterface::TAG_CONTEXT_DATA_RESOLVER));
    }
}
