<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection;

use Doctrine\Persistence\ManagerRegistry;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Interfaces\AppStateCheckerInterface;
use EonX\EasySwoole\Interfaces\AppStateResetterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Contracts\Service\ResetInterface;

final class EasySwooleExtension extends Extension
{
    private const ACCESS_LOG_CONFIG = [
        'timezone' => BridgeConstantsInterface::PARAM_ACCESS_LOG_TIMEZONE,
    ];

    private const EASY_BATCH_CONFIG = [
        'reset_batch_processor' => BridgeConstantsInterface::PARAM_RESET_EASY_BATCH_PROCESSOR,
    ];

    private const DOCTRINE_CONFIG = [
        'reset_dbal_connections' => BridgeConstantsInterface::PARAM_RESET_DOCTRINE_DBAL_CONNECTIONS,
    ];

    private const REQUEST_LIMITS_CONFIG = [
        'min' => BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MIN,
        'max' => BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MAX,
    ];

    private const STATIC_PHP_FILES_CONFIG = [
        'allowed_dirs' => BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_DIRS,
        'allowed_filenames' => BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_FILENAMES,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator([__DIR__ . '/../Resources/config']));
        $loader->load('services.php');

        $container
            ->registerForAutoconfiguration(AppStateCheckerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_CHECKER);

        $container
            ->registerForAutoconfiguration(AppStateResetterInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_RESETTER);

        if ($config['access_log']['enabled'] ?? true) {
            foreach (self::ACCESS_LOG_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['access_log'][$configName]);
            }

            $loader->load('access_log.php');
        }

        if (($config['doctrine']['enabled'] ?? true) && \interface_exists(ManagerRegistry::class)) {
            foreach (self::DOCTRINE_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['doctrine'][$configName]);
            }

            $loader->load('doctrine.php');
        }

        if ($config['easy_batch']['enabled'] ?? true) {
            foreach (self::EASY_BATCH_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['easy_batch'][$configName]);
            }
        }

        if ($config['request_limits']['enabled'] ?? true) {
            foreach (self::REQUEST_LIMITS_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['request_limits'][$configName]);
            }

            $loader->load('request_limits.php');
        }

        if (($config['reset_services']['enabled'] ?? true) && \interface_exists(ResetInterface::class)) {
            $loader->load('reset_services.php');
        }

        if ($config['static_php_files']['enabled'] ?? false) {
            foreach (self::STATIC_PHP_FILES_CONFIG as $configName => $param) {
                $container->setParameter($param, $config['static_php_files'][$configName]);
            }

            $loader->load('static_php_files.php');
        }
    }
}
