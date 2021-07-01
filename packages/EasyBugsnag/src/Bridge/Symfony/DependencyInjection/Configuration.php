<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_bugsnag');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('api_key')->isRequired()->end()
                ->booleanNode('aws_ecs_fargate')->defaultFalse()->end()
                ->scalarNode('aws_ecs_fargate_meta_url')
                    ->defaultValue('env(ECS_CONTAINER_METADATA_URI_V4)/task')
                ->end()
                ->scalarNode('aws_ecs_fargate_meta_storage_filename')
                    ->defaultValue('/var/www/var/aws_ecs_fargate_meta.json')
                ->end()
                ->arrayNode('doctrine_dbal')
                    ->beforeNormalization()
                        ->always(static function ($v): array {
                            return \is_array($v) ? $v : [
                                'enabled' => (bool)$v,
                            ];
                        })
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->arrayNode('connections')
                            ->beforeNormalization()->castToArray()->end()
                            ->defaultValue(['default'])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('session_tracking')->defaultFalse()->end()
                ->arrayNode('session_tracking_exclude')
                    ->beforeNormalization()->castToArray()->end()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('session_tracking_exclude_delimiter')->defaultValue('#')->end()
                ->booleanNode('worker_info')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
