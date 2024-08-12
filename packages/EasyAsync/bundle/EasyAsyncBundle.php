<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bundle;

use EonX\EasyAsync\Bundle\CompilerPass\ReorderMessengerMiddlewareCompilerPass;
use EonX\EasyAsync\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyAsyncBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        // -11 to run after easy-batch pass so middleware are first in the list
        $container->addCompilerPass(new ReorderMessengerMiddlewareCompilerPass(), priority: -11);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/messenger.php');

        $this->registerDoctrineConfiguration($config, $container, $builder);
        $this->registerMessengerConfiguration($config, $container, $builder);
    }

    private function registerDoctrineConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['doctrine']['close_persistent_connections']['enabled']) {
            $container
                ->parameters()
                ->set(
                    ConfigParam::DoctrineClosePersistentConnectionsMaxIdleTime->value,
                    $config['doctrine']['close_persistent_connections']['max_idle_time']
                );

            $container->import('config/doctrine_persistent_connections.php');
        }
    }

    private function registerMessengerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $this->registerMessengerMiddlewareConfiguration($config, $container, $builder);
        $this->registerStopOnMessagesLimitConfiguration($config, $container, $builder);
        $this->registerStopOnTimeLimitConfiguration($config, $container, $builder);
    }

    private function registerMessengerMiddlewareConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container
            ->parameters()
            ->set(
                ConfigParam::MessengerWorkerMiddlewareEnabled->value,
                $config['messenger']['middleware']['enabled']
            );

        if ($config['messenger']['middleware']['enabled'] === false) {
            return;
        }

        $container->import('config/messenger_middlewares.php');
    }

    private function registerStopOnMessagesLimitConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['messenger']['worker']['stop_on_messages_limit'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::MessengerWorkerStopMinMessages->value, $config['min_messages'])
            ->set(ConfigParam::MessengerWorkerStopMaxMessages->value, $config['max_messages']);

        $container->import('config/messenger_stop_on_messages_limit.php');
    }

    private function registerStopOnTimeLimitConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['messenger']['worker']['stop_on_time_limit'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::MessengerWorkerStopMinTime->value, $config['min_time'])
            ->set(ConfigParam::MessengerWorkerStopMaxTime->value, $config['max_time']);

        $container->import('config/messenger_stop_on_time_limit.php');
    }
}
