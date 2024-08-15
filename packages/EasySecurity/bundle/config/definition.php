<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('easy_bugsnag')
                ->canBeDisabled()
            ->end()
            ->scalarNode('token_decoder')->defaultNull()->end()
            ->arrayNode('permissions_locations')
                ->scalarPrototype()->end()
                ->beforeNormalization()->castToArray()->end()
            ->end()
            ->arrayNode('roles_locations')
                ->scalarPrototype()->end()
                ->beforeNormalization()->castToArray()->end()
            ->end()
            ->arrayNode('voters')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('priority')->defaultValue(100)->end()
                    ->booleanNode('permission_voter')->defaultFalse()->end()
                    ->booleanNode('provider_voter')->defaultFalse()->end()
                    ->booleanNode('role_voter')->defaultFalse()->end()
                ->end()
            ->end()
            ->arrayNode('default_configurators')
                ->canBeDisabled()
                ->children()
                    ->integerNode('priority')->defaultValue(-100)->end()
                ->end()
            ->end()
        ->end();
};
