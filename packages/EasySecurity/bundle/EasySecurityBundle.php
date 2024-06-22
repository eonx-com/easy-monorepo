<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface as EasyBugsnagBridgeConstantsInterface;
use EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface;
use EonX\EasySecurity\Authorization\Provider\RolesProviderInterface;
use EonX\EasySecurity\Bundle\CompilerPass\RegisterPermissionExpressionFunctionCompilerPass;
use EonX\EasySecurity\Bundle\CompilerPass\RegisterRoleExpressionFunctionCompilerPass;
use EonX\EasySecurity\Bundle\Enum\ConfigParam;
use EonX\EasySecurity\Bundle\Enum\ConfigTag;
use EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\SymfonySecurity\Voter\PermissionVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\ProviderVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\RoleVoter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasySecurityBundle extends AbstractBundle
{
    private const AUTO_CONFIG_TAGS = [
        PermissionsProviderInterface::class => ConfigTag::PermissionsProvider,
        RolesProviderInterface::class => ConfigTag::RolesProvider,
        SecurityContextConfiguratorInterface::class => ConfigTag::ContextConfigurator,
    ];

    private const VOTERS = [
        'permission' => PermissionVoter::class,
        'provider' => ProviderVoter::class,
        'role' => RoleVoter::class,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterPermissionExpressionFunctionCompilerPass());
        $container->addCompilerPass(new RegisterRoleExpressionFunctionCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $permissionsLocations = $config['permissions_locations'] ?? [];
        $rolesLocations = $config['roles_locations'] ?? [];

        $container
            ->parameters()
            ->set(ConfigParam::PermissionsLocations->value, $permissionsLocations);
        $container
            ->parameters()
            ->set(ConfigParam::RolesLocations->value, $rolesLocations);
        $container
            ->parameters()
            ->set(ConfigParam::TokenDecoder->value, $config['token_decoder'] ?? null);

        foreach (self::AUTO_CONFIG_TAGS as $interface => $tag) {
            $builder
                ->registerForAutoconfiguration($interface)
                ->addTag($tag->value);
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
                    ConfigTag::SecurityVoter->value => [
                        [
                            'priority' => $config['voters']['priority'],
                        ],
                    ],
                ]);

            $builder->setDefinition($class, $voterDefinition);
        }

        // EasyBugsnag
        if (($config['easy_bugsnag'] ?? false) && \interface_exists(EasyBugsnagBridgeConstantsInterface::class)) {
            $container->import('config/easy_bugsnag.php');
        }

        // Default configurators
        if ($config['use_default_configurators'] ?? true) {
            $container->import('config/default_configurators.php');
        }
    }
}
