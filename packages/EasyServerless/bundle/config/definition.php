<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('assets_separate_domain')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('url')
                        ->defaultValue('%env(ASSETS_URL)%')
                    ->end()
                ->end()
            ->end()
        ->end();
};
