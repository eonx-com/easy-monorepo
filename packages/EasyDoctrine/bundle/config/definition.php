<?php
declare(strict_types=1);

use Psr\Log\LoggerInterface;
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
                            ->scalarNode('logger')->defaultValue(LoggerInterface::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssl')
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('ca_path')
                                ->defaultValue('%kernel.cache_dir%/rds-combined-ca-bundle.pem')
                            ->end()
                            ->scalarNode('mode')->defaultValue('verify-full')->end()
                            ->scalarNode('logger')->defaultValue(LoggerInterface::class)->end()
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
