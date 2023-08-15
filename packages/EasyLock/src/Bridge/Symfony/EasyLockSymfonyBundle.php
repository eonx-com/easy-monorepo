<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony;

use EonX\EasyLock\Bridge\BridgeConstantsInterface;
use EonX\EasyLock\Bridge\Symfony\DependencyInjection\Compiler\RegisterLockStoreServicePass;
use EonX\EasyLock\Bridge\Symfony\DependencyInjection\Compiler\RegisterMessengerMiddlewareCompilerPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyLockSymfonyBundle extends AbstractBundle
{
    protected string $extensionAlias = 'easy_lock';

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new RegisterLockStoreServicePass())
            // -9 to run before easy-async and easy-batch so middleware is after
            ->addCompilerPass(new RegisterMessengerMiddlewareCompilerPass(), priority: -9);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        $container
            ->parameters()
            ->set(BridgeConstantsInterface::PARAM_CONNECTION, $config['connection']);

        if (\class_exists(MessengerPass::class)) {
            $container
                ->parameters()
                ->set(
                    BridgeConstantsInterface::PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER,
                    $config['messenger_middleware_auto_register'] ?? true
                );

            $container->import(__DIR__ . '/Resources/config/messenger_middleware.php');
        }
    }
}
