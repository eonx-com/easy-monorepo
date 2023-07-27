<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

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
                // Application Name
                ->arrayNode('app_name')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('env_var')->defaultValue('APP_NAME')->end()
                    ->end()
                ->end()
                // Basics
                ->scalarNode('project_root')
                    ->defaultValue('%kernel.project_dir%/src')
                ->end()
                ->scalarNode('release_stage')
                    ->defaultValue('%env(APP_ENV)%')
                ->end()
                ->scalarNode('runtime')
                    ->defaultValue('symfony')
                ->end()
                ->scalarNode('runtime_version')
                    ->defaultValue(Kernel::VERSION)
                ->end()
                ->scalarNode('strip_path')
                    ->defaultValue('%kernel.project_dir%')
                ->end()
                // AWS ECS FARGATE
                ->arrayNode('aws_ecs_fargate')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('meta_url')
                            ->defaultNull()
                            ->info('URL used to fetch AWS ECS Fargate task metadata')
                        ->end()
                        ->scalarNode('meta_storage_filename')
                            ->defaultValue('%kernel.cache_dir%/aws_ecs_fargate_meta.json')
                            ->info('Filename to cache AWS ECS Fargate task metadata into')
                        ->end()
                    ->end()
                ->end()
                // Doctrine DBAL
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
                // Sensitive Data
                ->arrayNode('sensitive_data_sanitizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                // Session Tracking
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
                            ->info('Expiry for sessions cache in seconds')
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
                        ->booleanNode('messenger_message_count_for_sessions')
                            ->defaultFalse()
                            ->info('Enable/Disable session tracking for messenger messages')
                        ->end()
                    ->end()
                ->end()
                // Worker Info
                ->arrayNode('worker_info')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
                ->booleanNode('use_default_configurators')->defaultTrue()->end()
            ->end();

        return $treeBuilder;
    }
}
