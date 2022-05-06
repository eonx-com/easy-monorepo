<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_utils');

        $treeBuilder->getRootNode()
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
                ->arrayNode('sensitive_data')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->arrayNode('keys_to_mask')->scalarPrototype()->end()->end()
                        ->scalarNode('mask_pattern')->defaultNull()->end()
                        ->booleanNode('use_default_object_transformers')->defaultTrue()->end()
                        ->booleanNode('use_default_string_sanitizers')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
