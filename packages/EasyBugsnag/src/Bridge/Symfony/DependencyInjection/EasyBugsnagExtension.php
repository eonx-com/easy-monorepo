<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface;
use EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyBugsnagExtension extends Extension
{
    private const AWS_ECS_FARGATE_CONFIG = [
        'meta_storage_filename' => BridgeConstantsInterface::PARAM_AWS_ECS_FARGATE_META_STORAGE_FILENAME,
        'meta_url' => BridgeConstantsInterface::PARAM_AWS_ECS_FARGATE_META_URL,
    ];

    private const BASICS_CONFIG = [
        'project_root' => BridgeConstantsInterface::PARAM_PROJECT_ROOT,
        'release_stage' => BridgeConstantsInterface::PARAM_RELEASE_STAGE,
        'runtime' => BridgeConstantsInterface::PARAM_RUNTIME,
        'runtime_version' => BridgeConstantsInterface::PARAM_RUNTIME_VERSION,
        'strip_path' => BridgeConstantsInterface::PARAM_STRIP_PATH,
    ];

    private const SESSION_TRACKING_CONFIG = [
        'cache_directory' => BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_DIRECTORY,
        'cache_expires_after' => BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_EXPIRES_AFTER,
        'cache_namespace' => BridgeConstantsInterface::PARAM_SESSION_TRACKING_CACHE_NAMESPACE,
        'exclude_urls' => BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_URLS,
        'exclude_urls_delimiter' => BridgeConstantsInterface::PARAM_SESSION_TRACKING_EXCLUDE_URLS_DELIMITER,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // Disabled completely
        if (($config['enabled'] ?? true) === false) {
            return;
        }

        // Basics config
        foreach (self::BASICS_CONFIG as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        // Default configurators
        if ($config['use_default_configurators'] ?? true) {
            $loader->load('default_configurators.php');
        }

        $container->setParameter(BridgeConstantsInterface::PARAM_API_KEY, $config['api_key']);
        $container->setParameter(
            BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_ENABLED,
            $config['doctrine_dbal']['enabled'] ?? false
        );
        $container->setParameter(
            BridgeConstantsInterface::PARAM_DOCTRINE_DBAL_CONNECTIONS,
            $config['doctrine_dbal']['connections'] ?? 'default'
        );

        $container
            ->registerForAutoconfiguration(ClientConfiguratorInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR);

        if ($config['app_name']['enabled'] ?? false) {
            $container->setParameter(BridgeConstantsInterface::PARAM_APP_NAME_ENV_VAR, $config['app_name']['env_var']);
            $loader->load('app_name.php');
        }

        if ($config['aws_ecs_fargate']['enabled'] ?? false) {
            foreach (self::AWS_ECS_FARGATE_CONFIG as $name => $param) {
                $container->setParameter($param, $config['aws_ecs_fargate'][$name]);
            }

            $loader->load('aws_ecs_fargate.php');
        }

        $container->setParameter(
            BridgeConstantsInterface::PARAM_SENSITIVE_DATA_SANITIZER_ENABLED,
            $config['sensitive_data_sanitizer']['enabled'] ?? true
        );

        if ($config['session_tracking']['enabled'] ?? false) {
            foreach (self::SESSION_TRACKING_CONFIG as $name => $param) {
                $container->setParameter($param, $config['session_tracking'][$name]);
            }

            $loader->load('sessions.php');

            if ($config['session_tracking']['messenger_message_count_for_sessions'] ?? false) {
                $loader->load('sessions_messenger.php');
            }
        }

        if ($config['worker_info']['enabled'] ?? false) {
            $loader->load('worker.php');
        }
    }
}
