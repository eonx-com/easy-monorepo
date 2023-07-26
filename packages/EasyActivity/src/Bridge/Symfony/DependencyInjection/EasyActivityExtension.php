<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\DependencyInjection;

use EonX\EasyActivity\Bridge\BridgeConstantsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyActivityExtension extends Extension implements PrependExtensionInterface
{
    private const EASY_ACTIVITY_CONFIG = [
        'disallowed_properties' => BridgeConstantsInterface::PARAM_DISALLOWED_PROPERTIES,
        'easy_doctrine_subscriber_enabled' => BridgeConstantsInterface::PARAM_EASY_DOCTRINE_SUBSCRIBER_ENABLED,
        'subjects' => BridgeConstantsInterface::PARAM_SUBJECTS,
        'table_name' => BridgeConstantsInterface::PARAM_TABLE_NAME,
    ];

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        foreach (self::EASY_ACTIVITY_CONFIG as $name => $param) {
            $container->setParameter($param, $config[$name]);
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        if ($this->easyDoctrineBundleIsRegistered($container)) {
            $loader->load('easy-doctrine-bridge-services.php');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($this->easyDoctrineBundleIsRegistered($container) === false) {
            return;
        }

        $easyDoctrineBundleConfig = $container->getExtensionConfig('easy_doctrine')[0] ?? [];
        $easyDoctrineEntities = $easyDoctrineBundleConfig['deferred_dispatcher_entities'] ?? [];

        $configs = $container->getExtensionConfig($this->getAlias());

        $resolvingBag = $container->getParameterBag();
        $configs = $resolvingBag->resolveValue($configs);

        $config = $this->processConfiguration(new Configuration(), $configs);

        $easyDoctrinePrependedConfig = [
            'deferred_dispatcher_entities' => \array_diff(\array_keys($config['subjects']), $easyDoctrineEntities),
        ];

        $container->prependExtensionConfig('easy_doctrine', $easyDoctrinePrependedConfig);
    }

    private function easyDoctrineBundleIsRegistered(ContainerBuilder $container): bool
    {
        /** @var array<string, string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');

        return isset($bundles['EasyDoctrineSymfonyBundle']);
    }
}
