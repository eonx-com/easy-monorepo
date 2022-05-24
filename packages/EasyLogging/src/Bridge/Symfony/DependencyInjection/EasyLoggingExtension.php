<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Bridge\Symfony\DependencyInjection;

use EonX\EasyLogging\Bridge\BridgeConstantsInterface;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use EonX\EasyLogging\Interfaces\Config\LoggerConfiguratorInterface;
use EonX\EasyLogging\Interfaces\Config\ProcessorConfigProviderInterface;
use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Logger as SymfonyBridgeLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyLoggingExtension extends Extension
{
    /**
     * @var string[]
     */
    private const AUTO_CONFIGS = [
        HandlerConfigProviderInterface::class => BridgeConstantsInterface::TAG_HANDLER_CONFIG_PROVIDER,
        LoggerConfiguratorInterface::class => BridgeConstantsInterface::TAG_LOGGER_CONFIGURATOR,
        ProcessorConfigProviderInterface::class => BridgeConstantsInterface::TAG_PROCESSOR_CONFIG_PROVIDER,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $container->setParameter(
            BridgeConstantsInterface::PARAM_DEFAULT_CHANNEL,
            $config['default_channel'] ?? LoggerFactoryInterface::DEFAULT_CHANNEL
        );

        $container->setParameter(
            BridgeConstantsInterface::PARAM_LOGGER_CLASS,
            \class_exists(SymfonyBridgeLogger::class) ? SymfonyBridgeLogger::class : Logger::class
        );

        $container->setParameter(BridgeConstantsInterface::PARAM_STREAM_HANDLER, $config['stream_handler']);
        $container->setParameter(BridgeConstantsInterface::PARAM_STREAM_HANDLER_LEVEL, $config['stream_handler_level']);

        foreach (self::AUTO_CONFIGS as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)
                ->addTag($tag);
        }

        $container->setParameter(
            BridgeConstantsInterface::PARAM_SENSITIVE_DATA_SANITIZER_ENABLED,
            $config['sensitive_data_sanitizer']['enabled'] ?? false
        );
    }
}
