<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bundle;

use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface;
use EonX\EasySecurity\Authorization\Provider\RolesProviderInterface;
use EonX\EasySecurity\Bundle\CompilerPass\RegisterPermissionExpressionFunctionCompilerPass;
use EonX\EasySecurity\Bundle\CompilerPass\RegisterRoleExpressionFunctionCompilerPass;
use EonX\EasySecurity\Bundle\Controller\EasySecurityLoginController;
use EonX\EasySecurity\Bundle\Enum\ConfigParam;
use EonX\EasySecurity\Bundle\Enum\ConfigTag;
use EonX\EasySecurity\Common\Configurator\SecurityContextConfiguratorInterface;
use EonX\EasySecurity\SymfonySecurity\Voter\PermissionVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\ProviderVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\RoleVoter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasySecurityBundle extends AbstractBundle
{
    private const array AUTO_CONFIG_TAGS = [
        PermissionsProviderInterface::class => ConfigTag::PermissionsProvider,
        RolesProviderInterface::class => ConfigTag::RolesProvider,
        SecurityContextConfiguratorInterface::class => ConfigTag::ContextConfigurator,
    ];

    private const array VOTERS = [
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
        foreach (self::AUTO_CONFIG_TAGS as $interface => $tag) {
            $builder
                ->registerForAutoconfiguration($interface)
                ->addTag($tag->value);
        }

        $container
            ->parameters()
            ->set(ConfigParam::PermissionsLocations->value, $config['permissions_locations'])
            ->set(ConfigParam::RolesLocations->value, $config['roles_locations'])
            ->set(ConfigParam::TokenDecoder->value, $config['token_decoder']);

        $container->import('config/services.php');

        $this->registerEasyBugsnagConfiguration($config, $container, $builder);
        $this->registerEasyLoggingConfiguration($container, $builder);
        $this->registerDefaultConfiguratorsConfiguration($config, $container, $builder);
        $this->registerVotersConfiguration($config, $container, $builder);
        $this->registerLoginFormConfiguration($config, $container, $builder);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function registerDefaultConfiguratorsConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['default_configurators']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::DefaultConfiguratorsPriority->value, $config['default_configurators']['priority']);

        $container->import('config/default_configurators.php');
    }

    private function registerEasyBugsnagConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['easy_bugsnag']['enabled'] === false) {
            return;
        }

        if ($this->isBundleEnabled('EasyBugsnagBundle', $builder) === false) {
            throw new LogicException('EasyBugsnagBundle is required to enable EasyBugsnag integration.');
        }

        $container->import('config/easy_bugsnag.php');
    }

    private function registerEasyLoggingConfiguration(
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if (\interface_exists(LoggerFactoryInterface::class) === false
            || $this->isBundleEnabled('EasyLoggingBundle', $builder) === false) {
            return;
        }

        $container->import('config/easy_logging.php');
    }

    private function registerLoginFormConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['login_form']['enabled'] === false) {
            return;
        }

        $builder->setDefinition(
            EasySecurityLoginController::class,
            new Definition(EasySecurityLoginController::class)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setArgument(
                    '$authenticationSuccessHandler',
                    new Reference(\sprintf(
                        'security.authentication.success_handler.%s.form_login',
                        $config['login_form']['firewall_name']
                    ))
                )
        );
    }

    private function registerVotersConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        foreach (self::VOTERS as $name => $class) {
            $configName = \sprintf('%s_voter', $name);

            if ($config['voters'][$configName] === false) {
                continue;
            }

            $voterDefinition = new Definition($class)
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
    }
}
