<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony;

use EonX\EasyBugsnag\Bundle\Enum\ConfigTag as EasyBugsnagConfigTag;
use EonX\EasySecurity\Bridge\BridgeConstantsInterface;
use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterPermissionExpressionFunctionPass;
use EonX\EasySecurity\Bridge\Symfony\DependencyInjection\Compiler\RegisterRoleExpressionFunctionPass;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\PermissionVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\ProviderVoter;
use EonX\EasySecurity\Bridge\Symfony\Security\Voters\RoleVoter;
use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;
use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasySecuritySymfonyBundle extends AbstractBundle
{
    private const AUTO_CONFIG_TAGS = [
        PermissionsProviderInterface::class => BridgeConstantsInterface::TAG_PERMISSIONS_PROVIDER,
        RolesProviderInterface::class => BridgeConstantsInterface::TAG_ROLES_PROVIDER,
        SecurityContextConfiguratorInterface::class => BridgeConstantsInterface::TAG_CONTEXT_CONFIGURATOR,
    ];

    private const VOTERS = [
        'permission' => PermissionVoter::class,
        'provider' => ProviderVoter::class,
        'role' => RoleVoter::class,
    ];

    protected string $extensionAlias = 'easy_security';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterPermissionExpressionFunctionPass());
        $container->addCompilerPass(new RegisterRoleExpressionFunctionPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        $permissionsLocations = $config['permissions_locations'] ?? [];
        $rolesLocations = $config['roles_locations'] ?? [];

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_PERMISSIONS_LOCATIONS, $permissionsLocations);
        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_ROLES_LOCATIONS, $rolesLocations);
        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_TOKEN_DECODER, $config['token_decoder'] ?? null);

        foreach (self::AUTO_CONFIG_TAGS as $interface => $tag) {
            $builder
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

            $builder->setDefinition($class, $voterDefinition);
        }

        // EasyBugsnag
        if (($config['easy_bugsnag'] ?? false) && \enum_exists(EasyBugsnagConfigTag::class)) {
            $container->import(__DIR__ . '/Resources/config/easy_bugsnag.php');
        }

        // Default configurators
        if ($config['use_default_configurators'] ?? true) {
            $container->import(__DIR__ . '/Resources/config/default_configurators.php');
        }
    }
}
