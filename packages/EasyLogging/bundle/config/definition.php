<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('default_channel')->defaultValue('app')->end()
            ->arrayNode('lazy_loggers')
                ->scalarPrototype()->end()
            ->end()
            ->arrayNode('sensitive_data_sanitizer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                ->end()
            ->end()
            ->booleanNode('stream_handler')->defaultTrue()->end()
            ->integerNode('stream_handler_level')->defaultNull()->end()
            ->booleanNode('bugsnag_handler')->defaultFalse()->end()
            ->integerNode('bugsnag_handler_level')->defaultNull()->end()
        ->end();
};
