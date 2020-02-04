<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextAwareInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Bridge\Symfony\Resolvers\DecoratorContextResolver;
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

        \var_dump($config);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Register context data resolvers for auto tagging
        $container->registerForAutoconfiguration(ContextDataResolverInterface::class)->addTag(TagsInterface::TAG_CONTEXT_DATA_RESOLVER);

        $this->registerContextResolver($container, $config);
        $this->registerDeferredContextResolver($container, $config);
        $this->registerContextResolverDecorator($container, $config);
        $this->registerContext($container, $config);
    }

    private function registerContext(ContainerBuilder $container, array $config): void
    {
        $def = (new Definition())->setSynthetic(true)->setPublic(true);

        $container->setDefinition($config['context_service_id'], $def);
    }

    private function registerContextResolver(ContainerBuilder $container, array $config): void
    {
        // Define dependencies for context resolver
        $def = $container->getDefinition(ContextResolverInterface::class);

        // Use EasyApiToken package directly to build decoder
        $tokenDecoderDef = (new Definition(EasyApiTokenDecoderInterface::class))
            ->setFactory([new Reference(EasyApiTokenDecoderFactoryInterface::class), 'build'])
            ->setArguments([$config['token_decoder']]);

        $container->setDefinition(EasyApiTokenDecoderInterface::class, $tokenDecoderDef);

        $def->setArgument(3, new TaggedIteratorArgument(TagsInterface::TAG_CONTEXT_DATA_RESOLVER));
    }

    private function registerContextResolverDecorator(ContainerBuilder $container, array $config): void
    {
        $def = $container->getDefinition(DecoratorContextResolver::class);

        $def->setArgument(2, $config['context_service_id']);
    }

    private function registerDeferredContextResolver(ContainerBuilder $container, array $config): void
    {
        $def = $container->getDefinition(DeferredContextResolverInterface::class);

        $def->setArgument(1, $config['context_service_id']);

        // Inject resolver to all services depending on it
        $container
            ->registerForAutoconfiguration(DeferredContextAwareInterface::class)
            ->addMethodCall('setDeferredContextResolver', [new Reference(DeferredContextResolverInterface::class)]);
    }
}
