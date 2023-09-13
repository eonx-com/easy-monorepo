<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('connection')->defaultValue('doctrine.dbal.default_connection')->end()
            ->booleanNode('messenger_middleware_auto_register')->defaultTrue()->end()
        ->end();
};
