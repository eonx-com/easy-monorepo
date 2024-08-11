<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('connection')->defaultValue('doctrine.dbal.default_connection')->end()
            ->arrayNode('messenger')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('middleware')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('auto_register')->defaultTrue()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
