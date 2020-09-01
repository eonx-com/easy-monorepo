<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\DependencyInjection;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\PermissionVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\ProviderVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\RoleVoter;
use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;
use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\SecurityContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasySecurityExtension extends Extension
{
    /**
     * @var string[]
     */
    protected static $autoConfigTags = [
        RolesProviderInterface::class => BridgeConstantsInterface::TAG_ROLES_PROVIDER,
        PermissionsProviderInterface::class => BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER,
        ContextModifierInterface::class => BridgeConstantsInterface::TAG_CONTEXT_MODIFIER,
        SecurityContextConfiguratorInterface::class => BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR,
    ];

    /**
     * @var string[]
     */
    protected static $voters = [
        'permission' => PermissionVoter::class,
        'provider' => ProviderVoter::class,
        'role' => RoleVoter::class,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $contextServiceId = $config['context_service_id'];
        $permissionsLocations = $config['permissions_locations'] ?? [];

        $container->setParameter(BridgeConstantsInterface::PARAM_PERMISSIONS_LOCATIONS, $permissionsLocations);
        $container->setParameter(BridgeConstantsInterface::PARAM_CONTEXT_SERVICE_ID, $contextServiceId);
        $container->setParameter(BridgeConstantsInterface::PARAM_TOKEN_DECODER, $config['token_decoder'] ?? null);

        foreach (static::$autoConfigTags as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)->addTag($tag);
        }

        $container->setDefinition($contextServiceId, (new Definition(SecurityContext::class))->setPublic(true));

        if ($contextServiceId !== SecurityContextInterface::class) {
            $container->setAlias(SecurityContextInterface::class, $contextServiceId);
        }

        foreach (static::$voters as $name => $class) {
            $configName = \sprintf('%s_enabled', $name);

            if (($config['voters'][$configName] ?? false) === false) {
                continue;
            }

            $container->setDefinition($class, (new Definition($class))->setAutowired(true)->setAutoconfigured(true));
        }

        // EasyBugsnag
        if (($config['easy_bugsnag'] ?? false) && \interface_exists(EasyBugsnagBridgeConstantsInterface::class)) {
            $loader->load('easy_bugsnag.php');
        }
    }
}
