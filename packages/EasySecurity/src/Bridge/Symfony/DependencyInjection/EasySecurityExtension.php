<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextAwareInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextResolverInterface;
use EonX\EasySecurity\Bridge\Symfony\Interfaces\ParametersInterface;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\PermissionVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\ProviderVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\RoleVoter;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;
use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PHPFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class EasySecurityExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PHPFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        // Set permissions locations parameter
        $container->setParameter(ParametersInterface::PERMISSIONS_LOCATIONS, $config['permissions_locations'] ?? []);

        // Register roles/permissions providers for auto tagging
        $container
            ->registerForAutoconfiguration(RolesProviderInterface::class)
            ->addTag(TagsInterface::TAG_ROLES_PROVIDER);
        $container
            ->registerForAutoconfiguration(PermissionsProviderInterface::class)
            ->addTag(TagsInterface::TAG_PERMISSIONS_PROVIDER);

        // Register context modifiers for auto tagging
        $container
            ->registerForAutoconfiguration(ContextModifierInterface::class)
            ->addTag(TagsInterface::TAG_CONTEXT_MODIFIER);

        // Register context configurators for auto tagging
        $container
            ->registerForAutoconfiguration(SecurityContextConfiguratorInterface::class)
            ->addTag(TagsInterface::TAG_CONTEXT_CONFIGURATOR);

        $this->registerContextResolver($container, $config);
        $this->registerDeferredContextResolver($container, $config);
        $this->registerContext($container, $config);
        $this->registerVoters($container, $config);
    }

    /**
     * @param mixed[] $config
     */
    private function registerContext(ContainerBuilder $container, array $config): void
    {
        $def = (new Definition(SecurityContextInterface::class))
            ->setFactory([new Reference(SecurityContextFactoryInterface::class), 'create'])
            ->setPublic(true);

        $container->setAlias(ContextFactoryInterface::class, SecurityContextFactoryInterface::class);

        $container->setDefinition($config['context_service_id'], $def);
        $container->setAlias(ContextInterface::class, $config['context_service_id']);
        $container->setAlias(SecurityContextInterface::class, $config['context_service_id']);
    }

    /**
     * @param mixed[] $config
     */
    private function registerContextResolver(ContainerBuilder $container, array $config): void
    {
        // Define dependencies for context resolver
        $def = $container->getDefinition(SecurityContextResolverInterface::class);

        // Use EasyApiToken package directly to build decoder
        $tokenDecoderDef = (new Definition(EasyApiTokenDecoderInterface::class))
            ->setFactory([new Reference(EasyApiTokenDecoderFactoryInterface::class), 'build'])
            ->setArguments([$config['token_decoder']]);

        $container->setDefinition(EasyApiTokenDecoderInterface::class, $tokenDecoderDef);

        $def->setArgument('$contextModifiers', new TaggedIteratorArgument(TagsInterface::TAG_CONTEXT_MODIFIER));
        $def->setArgument('$contextConfigurators', new TaggedIteratorArgument(TagsInterface::TAG_CONTEXT_CONFIGURATOR));
    }

    /**
     * @param mixed[] $config
     */
    private function registerDeferredContextResolver(ContainerBuilder $container, array $config): void
    {
        $def = $container->getDefinition(DeferredSecurityContextResolverInterface::class);

        $def->setArgument(1, $config['context_service_id']);

        // Inject resolver to all services depending on it
        $container
            ->registerForAutoconfiguration(DeferredSecurityContextAwareInterface::class)
            ->addMethodCall(
                'setDeferredContextResolver',
                [new Reference(DeferredSecurityContextResolverInterface::class)]
            );
    }

    /**
     * @param mixed[] $config
     */
    private function registerVoters(ContainerBuilder $container, array $config): void
    {
        $voters = [
            'permission' => PermissionVoter::class,
            'provider' => ProviderVoter::class,
            'role' => RoleVoter::class,
        ];

        foreach ($voters as $name => $class) {
            $configName = \sprintf('%s_enabled', $name);

            if (($config['voters'][$configName] ?? false) === false) {
                continue;
            }

            $container->setDefinition($class, (new Definition($class))->setAutowired(true)->setAutoconfigured(true));
        }
    }
}
