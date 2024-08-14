<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bundle;

use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag;
use EonX\EasyBugsnag\Common\Configurator\ClientConfiguratorInterface;
use EonX\EasyBugsnag\Doctrine\Logger\QueryBreadcrumbLogger;
use EonX\EasyBugsnag\Doctrine\Middleware\BreadcrumbLoggerMiddleware;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyBugsnagBundle extends AbstractBundle
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
        $builder
            ->registerForAutoconfiguration(ClientConfiguratorInterface::class)
            ->addTag(ConfigTag::ClientConfigurator->value);

        $container
            ->parameters()
            ->set(ConfigParam::ApiKey->value, $config['api_key'])
            ->set(ConfigParam::ProjectRoot->value, $config['project_root'])
            ->set(ConfigParam::ReleaseStage->value, $config['release_stage'])
            ->set(ConfigParam::Runtime->value, $config['runtime'])
            ->set(ConfigParam::RuntimeVersion->value, $config['runtime_version'])
            ->set(ConfigParam::StripPath->value, $config['strip_path']);

        $container->import('config/services.php');

        if ($config['use_default_configurators']) {
            $container->import('config/default_configurators.php');
        }

        if ($config['sensitive_data_sanitizer']['enabled']) {
            $container->import('config/sensitive_data_sanitizer.php');
        }

        if ($config['worker_info']['enabled']) {
            $container->import('config/worker.php');
        }

        $this->registerAppNameConfiguration($config, $container, $builder);
        $this->registerAwsEcsFargateConfiguration($config, $container, $builder);
        $this->registerDoctrineDbalConfiguration($config, $container, $builder);
        $this->registerSessionTrackingConfiguration($config, $container, $builder);
    }

    private function registerAppNameConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        if ($config['app_name']['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AppNameEnvVar->value, $config['app_name']['env_var']);

        $container->import('config/app_name.php');
    }

    private function registerAwsEcsFargateConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['aws_ecs_fargate'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::AwsEcsFargateMetaStorageFilename->value, $config['meta_storage_filename'])
            ->set(ConfigParam::AwsEcsFargateMetaUrl->value, $config['meta_url']);

        $container->import('config/aws_ecs_fargate.php');
    }

    private function registerDoctrineDbalConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $parameters = $container->parameters();
        $parameters->set(ConfigParam::DoctrineDbalEnabled->value, $config['doctrine_dbal']['enabled']);

        if ($config['doctrine_dbal']['enabled'] === false) {
            return;
        }

        foreach ($config['doctrine_dbal']['connections'] as $connection) {
            $builder->setDefinition(
                'easy_bugsnag.doctrine.middleware.' . $connection,
                (new Definition(
                    BreadcrumbLoggerMiddleware::class,
                    [$builder->getDefinition(QueryBreadcrumbLogger::class)]
                ))
                    ->addTag('doctrine.middleware', ['connection' => $connection])
            );
        }
    }

    private function registerSessionTrackingConfiguration(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $config = $config['session_tracking'];

        if ($config['enabled'] === false) {
            return;
        }

        $container
            ->parameters()
            ->set(ConfigParam::SessionTrackingCacheDirectory->value, $config['cache_directory'])
            ->set(ConfigParam::SessionTrackingCacheExpiresAfter->value, $config['cache_expires_after'])
            ->set(ConfigParam::SessionTrackingCacheNamespace->value, $config['cache_namespace'])
            ->set(ConfigParam::SessionTrackingExcludeUrls->value, $config['exclude_urls'])
            ->set(ConfigParam::SessionTrackingExcludeUrlsDelimiter->value, $config['exclude_urls_delimiter']);

        $container->import('config/sessions.php');

        if ($config['messenger_message_count_for_sessions']) {
            $container->import('config/sessions_messenger.php');
        }
    }
}
