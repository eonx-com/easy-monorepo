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
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enable/Disable the entire package')
                ->end()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->info('Bugsnag Notifier API key, can be found in project settings')
                ->end()
                ->arrayNode('aws_ecs_fargate')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('meta_url')
                            ->defaultValue('%env(ECS_CONTAINER_METADATA_URI_V4)%/task')
                            ->info('URL used to fetch AWS ECS Fargate task metadata')
                        ->end()
                        ->scalarNode('meta_storage_filename')
                            ->defaultValue('%kernel.cache_dir%/aws_ecs_fargate_meta.json')
                            ->info('Filename to cache AWS ECS Fargate task metadata into')
                        ->end()
                    ->end()
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
                ->arrayNode('session_tracking')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('cache_directory')
                            ->defaultValue('%kernel.cache_dir%')
                            ->info('Directory used by default cache adapter provided by the package')
                        ->end()
                        ->integerNode('cache_expires_after')
                            ->defaultValue(3600)
                            ->info('Expiry for sessions cache in minutes')
                        ->end()
                        ->scalarNode('cache_namespace')
                            ->defaultValue('easy_bugsnag_sessions')
                            ->info('Namespace used by default cache adapter provided by the package')
                        ->end()
                        ->arrayNode('exclude_urls')
                            ->beforeNormalization()->castToArray()->end()
                            ->scalarPrototype()->end()
                            ->info('List of URLs or Regex to exclude from session tracking')
                        ->end()
                        ->scalarNode('exclude_urls_delimiter')
                            ->defaultValue('#')
                            ->info('Delimiter used in Regex to resolve excluded URLs')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('worker_info')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
