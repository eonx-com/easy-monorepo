<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use EonX\EasySwoole\Bridge\BridgeConstantsInterface;
use EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler\AddDoctrineDbalConnectionNameToParamsPass;
use EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler\ResetEasyBatchProcessorPass;
use EonX\EasySwoole\Bridge\Symfony\DependencyInjection\Compiler\SymfonyServicesResetPass;
use EonX\EasySwoole\Interfaces\AppStateCheckerInterface;
use EonX\EasySwoole\Interfaces\AppStateInitializerInterface;
use EonX\EasySwoole\Interfaces\AppStateResetterInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Contracts\Service\ResetInterface;

final class EasySwooleSymfonyBundle extends AbstractBundle
{
    private const ACCESS_LOG_CONFIG = [
        'do_not_log_paths' => BridgeConstantsInterface::PARAM_ACCESS_LOG_DO_NOT_LOG_PATHS,
        'timezone' => BridgeConstantsInterface::PARAM_ACCESS_LOG_TIMEZONE,
    ];

    private const DOCTRINE_CONFIG = [
        'reset_dbal_connections' => BridgeConstantsInterface::PARAM_RESET_DOCTRINE_DBAL_CONNECTIONS,
    ];

    private const DOCTRINE_COROUTINE_PDO_CONFIG = [
        'default_heartbeat' => BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_HEARTBEAT,
        'default_max_idle_time' => BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_MAX_IDLE_TIME,
        'default_pool_size' => BridgeConstantsInterface::PARAM_DOCTRINE_COROUTINE_PDO_DEFAULT_POOL_SIZE,
    ];

    private const EASY_BATCH_CONFIG = [
        'reset_batch_processor' => BridgeConstantsInterface::PARAM_RESET_EASY_BATCH_PROCESSOR,
    ];

    private const REQUEST_LIMITS_CONFIG = [
        'max' => BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MAX,
        'min' => BridgeConstantsInterface::PARAM_REQUEST_LIMITS_MIN,
    ];

    private const STATIC_PHP_FILES_CONFIG = [
        'allowed_dirs' => BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_DIRS,
        'allowed_filenames' => BridgeConstantsInterface::PARAM_STATIC_PHP_FILES_ALLOWED_FILENAMES,
    ];

    protected string $extensionAlias = 'easy_swoole';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new AddDoctrineDbalConnectionNameToParamsPass())
            ->addCompilerPass(new ResetEasyBatchProcessorPass())
            ->addCompilerPass(new SymfonyServicesResetPass(), priority: -33);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        $builder
            ->registerForAutoconfiguration(AppStateCheckerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_CHECKER);

        $builder
            ->registerForAutoconfiguration(AppStateInitializerInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_INITIALIZER);

        $builder
            ->registerForAutoconfiguration(AppStateResetterInterface::class)
            ->addTag(BridgeConstantsInterface::TAG_APP_STATE_RESETTER);

        if ($config['access_log']['enabled'] ?? true) {
            foreach (self::ACCESS_LOG_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param, $config['access_log'][$configName]);
            }

            $container->import(__DIR__ . '/Resources/config/access_log.php');
        }

        if (($config['doctrine']['enabled'] ?? true) && \interface_exists(ManagerRegistry::class)) {
            foreach (self::DOCTRINE_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param, $config['doctrine'][$configName]);
            }

            if ($config['doctrine']['coroutine_pdo']['enabled'] ?? false) {
                foreach (self::DOCTRINE_COROUTINE_PDO_CONFIG as $configName => $param) {
                    $container
                        ->parameters()
                        ->set($param, $config['doctrine']['coroutine_pdo'][$configName]);
                }

                $container->import(__DIR__ . '/Resources/config/doctrine_coroutine_pdo.php');
            }

            $container->import(__DIR__ . '/Resources/config/doctrine.php');
        }

        if (($config['easy_admin']['enabled'] ?? true) && \class_exists(EA::class)) {
            $container->import(__DIR__ . '/Resources/config/easy_admin.php');
        }

        if ($config['easy_batch']['enabled'] ?? true) {
            foreach (self::EASY_BATCH_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param, $config['easy_batch'][$configName]);
            }
        }

        if (($config['easy_bugsnag']['enabled'] ?? true)
            && \interface_exists(ClientConfiguratorInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/easy_bugsnag.php');
        }

        if (($config['easy_logging']['enabled'] ?? true)
            && \interface_exists(ProcessorConfigProviderInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/easy_logging.php');
        }

        if ($config['request_limits']['enabled'] ?? true) {
            foreach (self::REQUEST_LIMITS_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param, $config['request_limits'][$configName]);
            }

            $container->import(__DIR__ . '/Resources/config/request_limits.php');
        }

        if (($config['reset_services']['enabled'] ?? true) && \interface_exists(ResetInterface::class)) {
            $container->import(__DIR__ . '/Resources/config/reset_services.php');
        }

        if ($config['static_php_files']['enabled'] ?? false) {
            foreach (self::STATIC_PHP_FILES_CONFIG as $configName => $param) {
                $container
                    ->parameters()
                    ->set($param, $config['static_php_files'][$configName]);
            }

            $container->import(__DIR__ . '/Resources/config/static_php_files.php');
        }
    }
}
