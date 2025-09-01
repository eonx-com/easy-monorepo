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
                            ->stringNode('aws_region')->defaultValue('ap-southeast-2')->end()
                            ->stringNode('aws_username')->defaultNull()->end()
                            ->integerNode('auth_token_lifetime_in_minutes')->defaultValue(10)->end()
                            ->stringNode('logger')->defaultValue(LoggerInterface::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssl')
                        ->canBeEnabled()
                        ->children()
                            ->stringNode('ca_path')
                                ->defaultValue('%kernel.cache_dir%/rds-combined-ca-bundle.pem')
                            ->end()
                            ->stringNode('mode')->defaultValue('verify-full')->end()
                            ->stringNode('logger')->defaultValue(LoggerInterface::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('deferred_dispatcher_entities')
                ->defaultValue([])
                ->stringPrototype()->end()
            ->end()
            ->arrayNode('easy_error_handler')
                ->canBeDisabled()
            ->end()
        ->end();
};
