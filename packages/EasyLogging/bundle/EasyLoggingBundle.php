<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle;

use EonX\EasyLogging\Bundle\CompilerPass\DefaultStreamHandlerCompilerPass;
use EonX\EasyLogging\Bundle\CompilerPass\ReplaceChannelsDefinitionCompilerPass;
use EonX\EasyLogging\Bundle\CompilerPass\ValidateSensitiveDataSanitizerCompilerPass;
use EonX\EasyLogging\Bundle\Enum\BundleParam;
use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use EonX\EasyLogging\Configurator\LoggerConfiguratorInterface;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyLoggingBundle extends AbstractBundle
{
    private const AUTO_CONFIGS = [
        HandlerConfigProviderInterface::class => ConfigTag::HandlerConfigProvider,
        LoggerConfiguratorInterface::class => ConfigTag::LoggerConfigurator,
        ProcessorConfigProviderInterface::class => ConfigTag::ProcessorConfigProvider,
    ];

    private const PREPEND_OPTIONS = [
        'bugsnag_handler',
        'bugsnag_handler_channels',
        'use_symfony_monolog_bundle',
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
            ->addCompilerPass(new ValidateSensitiveDataSanitizerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($config['use_symfony_monolog_bundle'] && $this->isBundleEnabled('MonologBundle', $builder) === false) {
            throw new LogicException(
                'The "easy_logging.use_symfony_monolog_bundle" option is enabled, but symfony/monolog-bundle '
                . '(MonologBundle) is not registered. Register the bundle so it owns the "logger" service, '
                . 'or disable the option.'
            );
        }

        $container->import('config/services.php');

        $params = $container->parameters();

        $params->set(ConfigParam::UseSymfonyMonologBundle->value, $config['use_symfony_monolog_bundle']);

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

        if ($config['sensitive_data_sanitizer']['enabled']) {
            // The sanitizer service itself is validated by ValidateSensitiveDataSanitizerCompilerPass once all
            // extensions are loaded
            $container->import('config/sensitive_data_sanitizer.php');
        }

        if ($config['bugsnag_handler']) {
            $params->set(ConfigParam::BugsnagHandlerLevel->value, $config['bugsnag_handler_level']);

            $container->import('config/bugsnag_handler.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $config = $this->processPrependConfig($builder);

        if ($config['use_symfony_monolog_bundle'] === false || $config['bugsnag_handler'] === false) {
            return;
        }

        $handler = [
            'id' => BugsnagMonologHandler::class,
            'type' => 'service',
        ];

        if (\count($config['bugsnag_handler_channels']) > 0) {
            $handler['channels'] = $config['bugsnag_handler_channels'];
        }

        $builder->prependExtensionConfig('monolog', [
            'handlers' => [
                BundleParam::BugsnagHandlerName->value => $handler,
            ],
        ]);
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function processPrependConfig(ContainerBuilder $builder): array
    {
        $configs = [];

        /** @var array $config */
        foreach ($builder->getExtensionConfig($this->extensionAlias) as $config) {
            // Only the needed options: env placeholders in the others cannot be processed before the load phase
            $configs[] = \array_intersect_key($config, \array_flip(self::PREPEND_OPTIONS));
        }

        /** @var array $resolvedConfigs */
        $resolvedConfigs = $builder->getParameterBag()
            ->resolveValue($configs);

        return (new Processor())->processConfiguration(
            new Configuration($this, $builder, $this->extensionAlias),
            $resolvedConfigs
        );
    }
}
