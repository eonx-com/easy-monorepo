<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bundle;

use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyAsync\Bundle\CompilerPass\RegisterMessengerMiddlewareCompilerPass;
use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\DependencyInjection\MessengerPass;

final class EasyAsyncBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        // -11 to run after easy-batch pass so middleware are first in the list
        $container->addCompilerPass(new RegisterMessengerMiddlewareCompilerPass(), priority: -11);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (\class_exists(MessengerPass::class) === false) {
            return;
        }

        // Messenger Middleware auto register
        $container->parameters()
            ->set(
                ConfigParam::MessengerMiddlewareAutoRegister->value,
                $config['messenger_middleware_auto_register'] ?? true
            );

        $container->import(__DIR__ . '/config/messenger.php');

        if (($config['doctrine']['enabled'] ?? true) && \interface_exists(EntityManagerInterface::class)) {
            $container->import(__DIR__ . '/config/messenger_doctrine.php');

            if ($config['doctrine']['close_persistent_connections'] ?? true) {
                $container->parameters()
                    ->set(
                        ConfigParam::DoctrinePersistentConnectionsMaxIdleTime->value,
                        $config['doctrine']['persistent_connections_max_idle_time'] ?? 10.0
                    );

                $container->import(__DIR__ . '/config/doctrine_persistent_connections.php');
            }
        }

        // Stop Worker On Messages
        if ($config['messenger_worker']['stop_on_messages_limit']['enabled'] ?? false) {
            $container->parameters()
                ->set(
                    ConfigParam::MessengerWorkerStopMinMessages->value,
                    $config['messenger_worker']['stop_on_messages_limit']['min_messages']
                );

            $container->parameters()
                ->set(
                    ConfigParam::MessengerWorkerStopMaxMessages->value,
                    $config['messenger_worker']['stop_on_messages_limit']['max_messages']
                );

            $container->import(__DIR__ . '/config/messenger_stop_on_messages_limit.php');
        }

        // Stop Worker On Time
        if ($config['messenger_worker']['stop_on_time_limit']['enabled'] ?? false) {
            $container->parameters()
                ->set(
                    ConfigParam::MessengerWorkerStopMinTime->value,
                    $config['messenger_worker']['stop_on_time_limit']['min_time']
                );

            $container->parameters()
                ->set(
                    ConfigParam::MessengerWorkerStopMaxTime->value,
                    $config['messenger_worker']['stop_on_time_limit']['max_time']
                );

            $container->import(__DIR__ . '/config/messenger_stop_on_time_limit.php');
        }
    }
}
