<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle;

use EonX\EasyLogging\Bundle\CompilerPass\DefaultStreamHandlerCompilerPass;
use EonX\EasyLogging\Bundle\CompilerPass\ReplaceChannelsDefinitionCompilerPass;
use EonX\EasyLogging\Bundle\CompilerPass\SensitiveDataSanitizerCompilerPass;
use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Configurator\LoggerConfiguratorInterface;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyLoggingBundle extends AbstractBundle
{
    private const AUTO_CONFIGS = [
        HandlerConfigProviderInterface::class => ConfigTag::HandlerConfigProvider,
        LoggerConfiguratorInterface::class => ConfigTag::LoggerConfigurator,
        ProcessorConfigProviderInterface::class => ConfigTag::ProcessorConfigProvider,
    ];

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new DefaultStreamHandlerCompilerPass())
            ->addCompilerPass(new ReplaceChannelsDefinitionCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10)
            ->addCompilerPass(new SensitiveDataSanitizerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('config/services.php');

        $params = $container->parameters();

        $params->set(ConfigParam::LazyLoggers->value, $config['lazy_loggers']);

        $params->set(ConfigParam::DefaultChannel->value, $config['default_channel']);

        $params->set(ConfigParam::LoggerClass->value, Logger::class);

        $params->set(ConfigParam::StreamHandler->value, $config['stream_handler']);
        $params->set(ConfigParam::StreamHandlerLevel->value, $config['stream_handler_level']);

        foreach (self::AUTO_CONFIGS as $interface => $tag) {
            $builder->registerForAutoconfiguration($interface)
                ->addTag($tag->value);
        }

        $params->set(ConfigParam::SensitiveDataSanitizerEnabled->value, $config['sensitive_data_sanitizer']['enabled']);

        if ($config['bugsnag_handler']) {
            $params->set(ConfigParam::BugsnagHandlerLevel->value, $config['bugsnag_handler_level']);

            $container->import('config/bugsnag_handler.php');
        }
    }
}
