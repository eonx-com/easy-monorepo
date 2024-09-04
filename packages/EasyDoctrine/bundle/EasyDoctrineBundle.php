<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Bundle;

use EonX\EasyDoctrine\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyDoctrineBundle extends AbstractBundle
{
    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $this->registerAwsRdsConfiguration($config, $container, $builder);
        $this->registerDeferredDispatcherConfiguration($config, $container, $builder);
        $this->registerEasyErrorHandlerConfiguration($config, $container, $builder);
    }

    private function registerAwsRdsConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['aws_rds']['iam']['enabled'] || $config['aws_rds']['ssl']['enabled']) {
            $container->import('config/aws_rds.php');
        }

        $this->registerAwsRdsIamConfiguration($config, $container, $builder);
        $this->registerAwsRdsSslConfiguration($config, $container, $builder);
    }

    private function registerAwsRdsIamConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_rds']['iam'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AwsRdsIamAuthTokenLifetimeInMinutes->value, $config['auth_token_lifetime_in_minutes'])
            ->set(ConfigParam::AwsRdsIamAwsRegion->value, $config['aws_region'])
            ->set(ConfigParam::AwsRdsIamAwsUsername->value, $config['aws_username']);

        $container->import('config/aws_rds_iam.php');
    }

    private function registerAwsRdsSslConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_rds']['ssl'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AwsRdsSslCaPath->value, $config['ca_path'])
            ->set(ConfigParam::AwsRdsSslMode->value, $config['mode']);

        $container->import('config/aws_rds_ssl.php');
    }

    private function registerDeferredDispatcherConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if (count($config['deferred_dispatcher_entities']) === 0) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::DeferredDispatcherEntities->value, $config['deferred_dispatcher_entities']);

        $container->import('config/deferred_dispatcher.php');
    }

    private function registerEasyErrorHandlerConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['easy_error_handler']['enabled'] === false) {
            return;
        }

        $container->import('config/easy_error_handler_listener.php');
    }
}
