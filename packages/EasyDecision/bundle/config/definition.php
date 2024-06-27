<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('use_expression_language')
                ->defaultValue(true)
                ->info('If set to true, the expression language will be set on all decisions automatically')
            ->end()
            ->arrayNode('type_mapping')
                ->prototype('scalar')
                ->validate()
                    ->ifTrue(static fn ($class): bool => \class_exists($class) === false)
                    ->thenInvalid('Class %s does not exist.')
                ->end()
                ->end()
                ->info(
                    'Decision type mapping to be used by ' .
                    '\EonX\EasyDecision\Interfaces\DecisionFactoryInterface::createByName'
                )
            ->end()
        ->end();
};
