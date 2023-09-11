<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('messenger_middleware_auto_register')->defaultTrue()->end()
            ->arrayNode('messenger_worker')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('stop_on_messages_limit')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->integerNode('min_messages')->end()
                            ->integerNode('max_messages')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->arrayNode('stop_on_time_limit')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->integerNode('min_time')->end()
                            ->integerNode('max_time')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
