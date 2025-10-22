<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EonX\EasyBugsnag\Common\Configurator\ClientConfiguratorInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use EonX\EasySwoole\Bundle\CompilerPass\AddDoctrineDbalConnectionNameToParamsCompilerPass;
use EonX\EasySwoole\Bundle\CompilerPass\EasyErrorHandlerPublicCompilerPass;
use EonX\EasySwoole\Bundle\CompilerPass\EasyEventDispatcherPublicCompilerPass;
use EonX\EasySwoole\Bundle\CompilerPass\ResetEasyBatchProcessorCompilerPass;
use EonX\EasySwoole\Bundle\CompilerPass\SymfonyServicesResetCompilerPass;
use EonX\EasySwoole\Bundle\Enum\ConfigParam;
use EonX\EasySwoole\Bundle\Enum\ConfigTag;
use EonX\EasySwoole\Common\Checker\AppStateCheckerInterface;
use EonX\EasySwoole\Common\Initializer\AppStateInitializerInterface;
use EonX\EasySwoole\Common\Resetter\AppStateResetterInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\Service\ResetInterface;

final class EasySwooleBundle extends AbstractBundle
{
    private const ACCESS_LOG_CONFIG = [
        'do_not_log_paths' => ConfigParam::AccessLogDoNotLogPaths,
        'timezone' => ConfigParam::AccessLogTimezone,
    ];

    private const DOCTRINE_CONFIG = [
        'reset_dbal_connections' => ConfigParam::ResetDoctrineDbalConnections,
    ];

    private const DOCTRINE_COROUTINE_PDO_CONFIG = [
        'default_heartbeat' => ConfigParam::DoctrineCoroutinePdoDefaultHeartbeat,
        'default_max_idle_time' => ConfigParam::DoctrineCoroutinePdoDefaultMaxIdleTime,
        'default_pool_size' => ConfigParam::DoctrineCoroutinePdoDefaultPoolSize,
    ];

    private const EASY_BATCH_CONFIG = [
        'reset_batch_processor' => ConfigParam::ResetEasyBatchProcessor,
    ];

    private const REQUEST_LIMITS_CONFIG = [
        'max' => ConfigParam::RequestLimitsMax,
        'min' => ConfigParam::RequestLimitsMin,
    ];

    private const STATIC_PHP_FILES_CONFIG = [
        'allowed_dirs' => ConfigParam::StaticPhpFilesAllowedDirs,
        'allowed_filenames' => ConfigParam::StaticPhpFilesAllowedFilenames,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AddDoctrineDbalConnectionNameToParamsCompilerPass())
            ->addCompilerPass(new EasyEventDispatcherPublicCompilerPass())
            ->addCompilerPass(new EasyErrorHandlerPublicCompilerPass())
            ->addCompilerPass(new ResetEasyBatchProcessorCompilerPass())
            ->addCompilerPass(new SymfonyServicesResetCompilerPass(), priority: -33);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $builder
            ->registerForAutoconfiguration(AppStateCheckerInterface::class)
            ->addTag(ConfigTag::AppStateChecker->value);

        $builder
            ->registerForAutoconfiguration(AppStateInitializerInterface::class)
            ->addTag(ConfigTag::AppStateInitializer->value);

        $builder
            ->registerForAutoconfiguration(AppStateResetterInterface::class)
            ->addTag(ConfigTag::AppStateResetter->value);

        if ($config['access_log']['enabled'] ?? true) {
            foreach (self::ACCESS_LOG_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['access_log'][$configName]);
            }

            $container->import('config/access_log.php');
        }

        if (($config['doctrine']['enabled'] ?? true) && \interface_exists(ManagerRegistry::class)) {
            foreach (self::DOCTRINE_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['doctrine'][$configName]);
            }

            if ($config['doctrine']['coroutine_pdo']['enabled'] ?? false) {
                foreach (self::DOCTRINE_COROUTINE_PDO_CONFIG as $configName => $param) {
                    $container
                        ->parameters()
                        ->set($param->value, $config['doctrine']['coroutine_pdo'][$configName]);
                }

                $container->import('config/doctrine_coroutine_pdo.php');
            }

            $container->import('config/doctrine.php');
        }

        if (($config['easy_admin']['enabled'] ?? true) && \class_exists(EA::class)) {
            $container->import('config/easy_admin.php');
        }

        if ($config['easy_batch']['enabled'] ?? true) {
            foreach (self::EASY_BATCH_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['easy_batch'][$configName]);
            }
        }

        if (($config['easy_bugsnag']['enabled'] ?? true)
            && \interface_exists(ClientConfiguratorInterface::class)) {
            $container->import('config/easy_bugsnag.php');
        }

        if (($config['easy_logging']['enabled'] ?? true)
            && \interface_exists(ProcessorConfigProviderInterface::class)) {
            $container->import('config/easy_logging.php');
        }

        if ($config['request_limits']['enabled'] ?? true) {
            foreach (self::REQUEST_LIMITS_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['request_limits'][$configName]);
            }

            $container->import('config/request_limits.php');
        }

        if (($config['reset_services']['enabled'] ?? true) && \interface_exists(ResetInterface::class)) {
            $container->import('config/reset_services.php');
        }

        if ($config['static_php_files']['enabled'] ?? false) {
            foreach (self::STATIC_PHP_FILES_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param->value, $config['static_php_files'][$configName]);
            }

            $container->import('config/static_php_files.php');
        }
    }
}
