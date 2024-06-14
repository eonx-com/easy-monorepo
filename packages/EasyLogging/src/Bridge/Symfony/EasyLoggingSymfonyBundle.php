<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\DefaultStreamHandlerPass;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\ReplaceChannelsDefinitionPass;
use EonX\EasyLogging\Bridge\Symfony\DependencyInjection\Compiler\SensitiveDataSanitizerCompilerPass;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Logger as SymfonyBridgeLogger;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyLoggingSymfonyBundle extends AbstractBundle
{
    private const AUTO_CONFIGS = [
        HandlerConfigProviderInterface::class => BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER,
        LoggerConfiguratorInterface::class => BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR,
        ProcessorConfigProviderInterface::class => BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER,
    ];

    protected string $extensionAlias = 'easy_logging';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DefaultStreamHandlerPass())
            ->addCompilerPass(new ReplaceChannelsDefinitionPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10)
            ->addCompilerPass(new SensitiveDataSanitizerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__ . '/Resources/config/services.php');

        $params = $container->parameters();

        $params->set(BridgeConstantsInterface::PARAM_LAZY_LOGGERS, $config['lazy_loggers'] ?? []);

        $params->set(
            BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL,
            $config['default_channel'] ?? LoggerFactoryInterface::DEFAULT_CHANNEL
        );

        $params->set(
            BridgeConstantsInterface::PARAM_LOGGER_CLASS,
            \class_exists(SymfonyBridgeLogger::class) ? SymfonyBridgeLogger::class : Logger::class
        );

        $params->set(BridgeConstantsInterface::PARAM_STREAM_HANDLER, $config['stream_handler']);
        $params->set(BridgeConstantsInterface::PARAM_STREAM_HANDLER_LEVEL, $config['stream_handler_level']);

        foreach (self::AUTO_CONFIGS as $interface => $tag) {
            $builder->registerForAutoconfiguration($interface)
                ->addTag($tag);
        }

        $params->set(
            BridgeConstantsInterface::PARAM_SENSITIVE_DATA_SANITIZER_ENABLED,
            $config['sensitive_data_sanitizer']['enabled'] ?? false
        );

        if ($config['bugsnag_handler'] ?? false) {
            $params->set(
                BridgeConstantsInterface::PARAM_BUGSNAG_HANDLER_LEVEL,
                $config['bugsnag_handler_level'] ?? null
            );

            $container->import(__DIR__ . '/Resources/config/bugsnag_handler.php');
        }
    }
}
