<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bundle;

use EonX\EasyBugsnag\Bundle\CompilerPass\DoctrineSqlLoggerConfiguratorCompilerPass;
use EonX\EasyBugsnag\Bundle\CompilerPass\SensitiveDataSanitizerCompilerPass;
use EonX\EasyBugsnag\Bundle\Enum\ConfigParam;
use EonX\EasyBugsnag\Bundle\Enum\ConfigTag;
use EonX\EasyBugsnag\Configurator\ClientConfiguratorInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyBugsnagBundle extends AbstractBundle
{
    private const AWS_ECS_FARGATE_CONFIG = [
        'meta_storage_filename' => ConfigParam::AwsEcsFargateMetaStorageFilename,
        'meta_url' => ConfigParam::AwsEcsFargateMetaUrl,
    ];

    private const BASICS_CONFIG = [
        'project_root' => ConfigParam::ProjectRoot,
        'release_stage' => ConfigParam::ReleaseStage,
        'runtime' => ConfigParam::Runtime,
        'runtime_version' => ConfigParam::RuntimeVersion,
        'strip_path' => ConfigParam::StripPath,
    ];

    private const SESSION_TRACKING_CONFIG = [
        'cache_directory' => ConfigParam::SessionTrackingCacheDirectory,
        'cache_expires_after' => ConfigParam::SessionTrackingCacheExpiresAfter,
        'cache_namespace' => ConfigParam::SessionTrackingCacheNamespace,
        'exclude_urls' => ConfigParam::SessionTrackingExcludeUrls,
        'exclude_urls_delimiter' => ConfigParam::SessionTrackingExcludeUrlsDelimiter,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DoctrineSqlLoggerConfiguratorCompilerPass())
            ->addCompilerPass(new SensitiveDataSanitizerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Disabled completely
        if (($config['enabled'] ?? true) === false) {
            return;
        }

        // Basics config
        foreach (self::BASICS_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$name]);
        }

        $container->import('config/services.php');

        // Default configurators
        if ($config['use_default_configurators'] ?? true) {
            $container->import('config/default_configurators.php');
        }

        $container
            ->parameters()
            ->set(ConfigParam::ApiKey->value, $config['api_key']);
        $container
            ->parameters()
            ->set(ConfigParam::DoctrineDbalEnabled->value, $config['doctrine_dbal']['enabled'] ?? false);
        $container
            ->parameters()
            ->set(
                ConfigParam::DoctrineDbalConnections->value,
                $config['doctrine_dbal']['connections'] ?? 'default'
            );

        $builder
            ->registerForAutoconfiguration(ClientConfiguratorInterface::class)
            ->addTag(ConfigTag::ClientConfigurator->value);

        if ($config['app_name']['enabled'] ?? false) {
            $container
                ->parameters()
                ->set(ConfigParam::AppNameEnvVar->value, $config['app_name']['env_var']);

            $container->import('config/app_name.php');
        }

        if ($config['aws_ecs_fargate']['enabled'] ?? false) {
            foreach (self::AWS_ECS_FARGATE_CONFIG as $name => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['aws_ecs_fargate'][$name]);
            }

            $container->import('config/aws_ecs_fargate.php');
        }

        $container
            ->parameters()
            ->set(
                ConfigParam::SensitiveDataSanitizerEnabled->value,
                $config['sensitive_data_sanitizer']['enabled'] ?? true
            );

        if ($config['session_tracking']['enabled'] ?? false) {
            foreach (self::SESSION_TRACKING_CONFIG as $name => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['session_tracking'][$name]);
            }

            $container->import('config/sessions.php');

            if ($config['session_tracking']['messenger_message_count_for_sessions'] ?? false) {
                $container->import('config/sessions_messenger.php');
            }
        }

        if ($config['worker_info']['enabled'] ?? false) {
            $container->import('config/worker.php');
        }
    }
}
