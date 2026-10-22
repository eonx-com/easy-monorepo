<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle;

use EonX\EasyLogging\Bundle\CompilerPass\ValidateLoggerFactoryServicesCompilerPass;
use EonX\EasyLogging\Bundle\CompilerPass\ValidateSensitiveDataSanitizerCompilerPass;
use EonX\EasyLogging\Bundle\Enum\BundleParam;
use EonX\EasyLogging\Bundle\Enum\ConfigParam;
use EonX\EasyLogging\MonologHandler\BugsnagMonologHandler;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyLoggingBundle extends AbstractBundle
{
    private const string MONOLOG_MAIN_CHANNEL = 'app';

    public function __construct()
    {
        $this->path = \realpath(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ValidateLoggerFactoryServicesCompilerPass())
            ->addCompilerPass(new ValidateSensitiveDataSanitizerCompilerPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($this->isBundleEnabled('MonologBundle', $builder) === false) {
            throw new LogicException(
                'EasyLoggingBundle requires symfony/monolog-bundle: install it (composer require '
                . 'symfony/monolog-bundle) and register MonologBundle so it owns the "logger" service.'
            );
        }

        $container->import('config/services.php');

        $params = $container->parameters();

        $params->set(ConfigParam::SensitiveDataSanitizerEnabled->value, $config['sensitive_data_sanitizer']['enabled']);

        if ($config['sensitive_data_sanitizer']['enabled']) {
            // The sanitizer service itself is validated by ValidateSensitiveDataSanitizerCompilerPass once all
            // extensions are loaded
            $container->import('config/sensitive_data_sanitizer.php');
        }

        if ($config['bugsnag_handler']['enabled']) {
            $params->set(ConfigParam::BugsnagHandlerLevel->value, $config['bugsnag_handler']['level']);

            $container->import('config/bugsnag_handler.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $bugsnagHandlerConfigs = $this->getRawBugsnagHandlerConfigs($builder);

        if (
            $this->isBugsnagHandlerEnabled($bugsnagHandlerConfigs) === false
            || $this->isBugsnagHandlerDeclaredByApplication($builder)
        ) {
            return;
        }

        $channels = $this->getBugsnagHandlerChannels($bugsnagHandlerConfigs);

        if (\count($channels) === 0) {
            $channels = [self::MONOLOG_MAIN_CHANNEL];
        }

        $builder->prependExtensionConfig('monolog', [
            'handlers' => [
                BundleParam::BugsnagHandlerName->value => [
                    'channels' => $channels,
                    'id' => BugsnagMonologHandler::class,
                    'type' => 'service',
                ],
            ],
        ]);
    }

    /**
     * @param list<array|scalar|null> $bugsnagHandlerConfigs
     *
     * @return list<string>
     */
    private function getBugsnagHandlerChannels(array $bugsnagHandlerConfigs): array
    {
        $channels = [];

        foreach ($bugsnagHandlerConfigs as $bugsnagHandlerConfig) {
            if (\is_array($bugsnagHandlerConfig)) {
                $channels = [
                    ...$channels,
                    ...\array_filter((array)($bugsnagHandlerConfig['channels'] ?? []), \is_string(...)),
                ];
            }
        }

        return \array_values(\array_unique($channels));
    }

    /**
     * The "bugsnag_handler" node of every "easy_logging" config, in loading order. Prepend runs before the config
     * tree processes them, so the values are raw: a boolean shorthand, null or an array with "enabled", "level"
     * and "channels" keys.
     *
     * @return list<array|scalar|null>
     */
    private function getRawBugsnagHandlerConfigs(ContainerBuilder $builder): array
    {
        $bugsnagHandlerConfigs = [];

        foreach ($builder->getExtensionConfig($this->extensionAlias) as $config) {
            if (\array_key_exists('bugsnag_handler', $config)) {
                /** @var array|scalar|null $bugsnagHandlerConfig */
                $bugsnagHandlerConfig = $this->resolveParameterSafely($builder, $config['bugsnag_handler']);

                $bugsnagHandlerConfigs[] = $bugsnagHandlerConfig;
            }
        }

        return $bugsnagHandlerConfigs;
    }

    private function isBugsnagHandlerDeclaredByApplication(ContainerBuilder $builder): bool
    {
        /** @var array $config */
        foreach ($builder->getExtensionConfig('monolog') as $config) {
            /** @var array $handler */
            foreach ($config['handlers'] ?? [] as $handler) {
                $serviceId = $this->resolveParameterSafely($builder, $handler['id'] ?? null);

                if (
                    $this->resolveParameterSafely($builder, $handler['type'] ?? null) !== 'service'
                    || \is_string($serviceId) === false
                ) {
                    continue;
                }

                $class = $serviceId;

                if ($builder->has($serviceId)) {
                    $definition = $builder->findDefinition($serviceId);
                    $class = $definition->getClass() ?? $serviceId;
                }

                if ($class === BugsnagMonologHandler::class) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param list<array|scalar|null> $bugsnagHandlerConfigs
     */
    private function isBugsnagHandlerEnabled(array $bugsnagHandlerConfigs): bool
    {
        $enabled = false;

        foreach ($bugsnagHandlerConfigs as $bugsnagHandlerConfig) {
            $enabled = match (true) {
                $bugsnagHandlerConfig === null => true,
                \is_array($bugsnagHandlerConfig) => (bool)($bugsnagHandlerConfig['enabled'] ?? true),
                default => (bool)$bugsnagHandlerConfig,
            };
        }

        return $enabled;
    }

    private function isBundleEnabled(string $bundleName, ContainerBuilder $builder): bool
    {
        /** @var array $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles[$bundleName]);
    }

    private function resolveParameterSafely(ContainerBuilder $builder, mixed $value): mixed
    {
        try {
            return $builder->getParameterBag()
                ->resolveValue($value);
        } catch (ParameterNotFoundException|RuntimeException) {
            // Parameters not defined yet (or not resolvable at this stage) must not break the compilation
            return $value;
        }
    }
}
