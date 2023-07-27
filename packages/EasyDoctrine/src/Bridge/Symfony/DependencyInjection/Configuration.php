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
                ->arrayNode('aws_rds')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('iam')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultNull()->end()
                                ->scalarNode('aws_region')->defaultNull()->end()
                                ->scalarNode('aws_username')->defaultNull()->end()
                                ->integerNode('auth_token_lifetime_in_minutes')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('ssl')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('ca_path')
                                    ->defaultValue('%kernel.cache_dir%/rds-combined-ca-bundle.pem')
                                    ->end()
                                ->scalarNode('mode')->defaultValue('verify-full')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('aws_rds_iam')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('aws_region')->defaultValue('ap-southeast-2')->end()
                        ->scalarNode('aws_username')->defaultValue('disabled')->end()
                        ->integerNode('cache_expiry_in_seconds')->defaultValue(600)->end()
                        ->booleanNode('ssl_enabled')->defaultFalse()->end()
                        ->scalarNode('ssl_mode')->defaultValue('verify-full')->end()
                        ->scalarNode('ssl_cert_dir')->defaultValue('%kernel.cache_dir%')->end()
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
