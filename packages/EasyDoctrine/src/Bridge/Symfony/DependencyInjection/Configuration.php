<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_doctrine');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('aws_rds_iam')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('aws_region')->defaultValue('ap-southeast-2')->end()
                        ->scalarNode('aws_username')->defaultValue('disabled')->end()
                        ->integerNode('cache_expiry_in_seconds')->defaultValue(600)->end()
                        ->scalarNode('disabled_username')->defaultValue('disabled')->end()
                    ->end()
                ->end()
                ->arrayNode('deferred_dispatcher_entities')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('easy_error_handler_enabled')
                    ->defaultValue(true)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
