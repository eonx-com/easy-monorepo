<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('aws_rds')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('iam')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('aws_region')->defaultValue('ap-southeast-2')->end()
                            ->scalarNode('aws_username')->defaultNull()->end()
                            ->integerNode('auth_token_lifetime_in_minutes')->defaultValue(10)->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssl')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('ca_path')->defaultValue('%kernel.cache_dir%')->end()
                            ->scalarNode('mode')->defaultValue('verify-full')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('deferred_dispatcher_entities')
                ->defaultValue([])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('easy_error_handler')
                ->canBeDisabled()
            ->end()
        ->end();
};
