<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->integerNode('uuid_version')
                ->validate()
                    ->ifNotInArray([1, 4, 6, 7])
                    ->thenInvalid('Invalid UUID version %s')
                ->end()
                ->defaultValue(6)
                ->info('Version of UUID to generate')
            ->end()
        ->end();
};
