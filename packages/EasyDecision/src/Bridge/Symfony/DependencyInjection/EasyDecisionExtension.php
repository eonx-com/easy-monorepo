<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony\DependencyInjection;

use EonX\EasyDecision\Bridge\Interfaces\TagsInterface;
use EonX\EasyDecision\Interfaces\DecisionConfiguratorInterface;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;
use EonX\EasyDecision\Providers\ConfigMappingProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class EasyDecisionExtension extends Extension
{
    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if ($config['use_expression_language'] ?? false) {
            $loader->load('use_expression_language.yaml');
        }

        $container
            ->registerForAutoconfiguration(DecisionConfiguratorInterface::class)
            ->addTag(TagsInterface::DECISION_CONFIGURATOR);

        $container
            ->autowire(MappingProviderInterface::class, ConfigMappingProvider::class)
            ->setArgument('$decisionsConfig', $config['type_mapping'] ?? []);
    }
}
