<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('aws_rds')
                ->addDefaultsIfNotSet()
                ->children()
                    // TODO: Set default values from "aws_rds_iam" in 6.0
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
                            ->booleanNode('enabled')->defaultNull()->end()
                            ->scalarNode('ca_path')->defaultNull()->end()
                            ->scalarNode('mode')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            // Deprecated since 5.3. Has to be removed in 6.0
            ->arrayNode('aws_rds_iam')
                ->setDeprecated(
                    'EasyDoctrine',
                    '5.3.0',
                    'The "%node%" node is deprecated, use "aws_rds.iam" and "aws_rds.ssl" instead.'
                )
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
};
