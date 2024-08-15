<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('math')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('round_precision')->defaultNull()->end()
                    ->integerNode('round_mode')->defaultNull()->end()
                    ->integerNode('scale')->defaultNull()->end()
                    ->scalarNode('format_decimal_separator')->defaultNull()->end()
                    ->scalarNode('format_thousands_separator')->defaultNull()->end()
                ->end()
            ->end()
            ->arrayNode('sensitive_data_sanitizer')
                ->canBeDisabled()
                ->children()
                    ->arrayNode('keys_to_mask')->scalarPrototype()->end()->end()
                    ->scalarNode('mask_pattern')->defaultValue('*REDACTED*')->end()
                    ->booleanNode('use_default_keys_to_mask')->defaultTrue()->end()
                    ->booleanNode('use_default_object_transformers')->defaultTrue()->end()
                    ->booleanNode('use_default_string_sanitizers')->defaultTrue()->end()
                ->end()
            ->end()
            ->arrayNode('string_trimmer')
                ->canBeEnabled()
                ->children()
                    ->arrayNode('except_keys')
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
