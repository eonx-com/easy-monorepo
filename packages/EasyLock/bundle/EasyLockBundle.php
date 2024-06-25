<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle;

use EonX\EasyLock\Bundle\CompilerPass\RegisterLockStoreServiceCompilerPass;
use EonX\EasyLock\Bundle\CompilerPass\RegisterMessengerMiddlewareCompilerPass;
use EonX\EasyLock\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

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
            ->addCompilerPass(new RegisterMessengerMiddlewareCompilerPass(), priority: -9);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $container
            ->parameters()
            ->set(ConfigParam::Connection->value, $config['connection']);

        if (\class_exists(MessengerPass::class)) {
            $container
                ->parameters()
                ->set(
                    ConfigParam::MessengerMiddlewareAutoRegister->value,
                    $config['messenger_middleware_auto_register'] ?? true
                );

            $container->import('config/messenger_middleware.php');
        }
    }
}
