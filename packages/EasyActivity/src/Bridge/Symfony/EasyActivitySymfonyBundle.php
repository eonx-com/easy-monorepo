<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony;

use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyActivitySymfonyBundle extends AbstractBundle
{
    private const EASY_ACTIVITY_CONFIG = [
        'disallowed_properties' => BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES,
        'easy_doctrine_subscriber_enabled' => BridgeConstantsInterface::PARAM_EASY_DOCTRINE_SUBSCRIBER_ENABLED,
        'subjects' => BridgeConstantsInterface::PARAM_SUBJECTS,
        'table_name' => BridgeConstantsInterface::PARAM_TABLE_NAME,
    ];

    protected string $extensionAlias = 'easy_activity';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/Resources/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_ACTIVITY_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param, $config[$name]);
        }

        $container->import(__DIR__ . '/Resources/config/services.php');

        if ($this->easyDoctrineBundleIsRegistered($builder)) {
            $container->import(__DIR__ . '/Resources/config/easy-doctrine-bridge-services.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if ($this->easyDoctrineBundleIsRegistered($builder) === false) {
            return;
        }

        $easyDoctrineBundleConfig = $builder->getExtensionConfig('easy_doctrine')[0] ?? [];
        $easyDoctrineEntities = $easyDoctrineBundleConfig['deferred_dispatcher_entities'] ?? [];

        $configs = $builder->getExtensionConfig($this->extensionAlias);

        $resolvingBag = $builder->getParameterBag();
        $configs = $resolvingBag->resolveValue($configs);

        $config = (new Processor())->processConfiguration(
            new Configuration($this, $builder, $this->extensionAlias),
            $configs
        );

        $easyDoctrinePrependedConfig = [
            'deferred_dispatcher_entities' => \array_diff(\array_keys($config['subjects']), $easyDoctrineEntities),
        ];

        $builder->prependExtensionConfig('easy_doctrine', $easyDoctrinePrependedConfig);
    }

    private function easyDoctrineBundleIsRegistered(ContainerBuilder $builder): bool
    {
        /** @var array<string, string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return isset($bundles['EasyDoctrineSymfonyBundle']);
    }
}
