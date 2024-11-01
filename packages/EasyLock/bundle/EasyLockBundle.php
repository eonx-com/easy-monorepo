<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle;

use EonX\EasyLock\Bundle\CompilerPass\RegisterLockStoreServiceCompilerPass;
use EonX\EasyLock\Bundle\CompilerPass\ReorderMessengerMiddlewareCompilerPass;
use EonX\EasyLock\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyLockBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new RegisterLockStoreServiceCompilerPass())
            // -9 to run before easy-async and easy-batch so middleware is after
            // @todo change to -10 in 7.0 to allow adding more middleware in the middle
            ->addCompilerPass(new ReorderMessengerMiddlewareCompilerPass(), priority: -9);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container
            ->parameters()
            ->set(ConfigParam::Connection->value, $config['connection']);

        $container->import('config/services.php');

        $this->registerMessengerConfiguration($config, $container, $builder);
    }

    private function registerMessengerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container
            ->parameters()
            ->set(
                ConfigParam::MessengerMiddlewareEnabled->value,
                $config['messenger']['middleware']['enabled']
            );

        if ($config['messenger']['middleware']['enabled'] === false) {
            return;
        }

        $container->import('config/messenger_middleware.php');
    }
}
