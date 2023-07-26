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
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
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
    private const AUTO_CONFIG_TAGS = [
        RolesProviderInterface::class => BridgeConstantsInterface::TAG_ROLES_PROVIDER,
        PermissionsProviderInterface::class => BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER,
        SecurityContextConfiguratorInterface::class => BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR,
    ];

    /**
     * @var string[]
     */
    private const VOTERS = [
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

        $permissionsLocations = $config['permissions_locations'] ?? [];
        $rolesLocations = $config['roles_locations'] ?? [];

        $container->setParameter(BridgeConstantsInterface::PARAM_PERMISSIONS_LOCATIONS, $permissionsLocations);
        $container->setParameter(BridgeConstantsInterface::PARAM_ROLES_LOCATIONS, $rolesLocations);
        $container->setParameter(BridgeConstantsInterface::PARAM_TOKEN_DECODER, $config['token_decoder'] ?? null);

        foreach (self::AUTO_CONFIG_TAGS as $interface => $tag) {
            $container
                ->registerForAutoconfiguration($interface)
                ->addTag($tag);
        }

        foreach (self::VOTERS as $name => $class) {
            $configName = \sprintf('%s_enabled', $name);

            if (($config['voters'][$configName] ?? false) === false) {
                continue;
            }

            $voterDefinition = (new Definition($class))
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setTags([
                    BridgeConstantsInterface::TAG_SECURITY_VOTER => [
                        [
                            'priority' => $config['voters']['priority'],
                        ],
                    ],
                ]);

            $container->setDefinition($class, $voterDefinition);
        }

        // EasyBugsnag
        if (($config['easy_bugsnag'] ?? false) && \interface_exists(EasyBugsnagBridgeConstantsInterface::class)) {
            $loader->load('easy_bugsnag.php');
        }

        // Default configurators
        if ($config['use_default_configurators'] ?? true) {
            $loader->load('default_configurators.php');
        }
    }
}
