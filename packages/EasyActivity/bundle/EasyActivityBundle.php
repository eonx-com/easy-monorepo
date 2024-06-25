<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bundle;

use EonX\EasyActivity\Bundle\Enum\ConfigParam;
use Symfony\Component\Config\Definition\Configuration;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EasyActivityBundle extends AbstractBundle
{
    private const EASY_ACTIVITY_CONFIG = [
        'disallowed_properties' => ConfigParam::DisallowedProperties,
        'easy_doctrine_subscriber_enabled' => ConfigParam::EasyDoctrineSubscriberEnabled,
        'subjects' => ConfigParam::Subjects,
        'table_name' => ConfigParam::TableName,
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__ . '/config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::EASY_ACTIVITY_CONFIG as $name => $param) {
            $container
                ->parameters()
                ->set($param->value, $config[$name]);
        }

        $container->import(__DIR__ . '/config/services.php');

        if ($this->easyDoctrineBundleIsRegistered($builder)) {
            $container->import(__DIR__ . '/config/easy-doctrine-bridge-services.php');
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

        return isset($bundles['EasyDoctrineBundle']);
    }
}
