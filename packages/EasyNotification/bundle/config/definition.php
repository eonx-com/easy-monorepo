<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('api_url')->end()
            ->integerNode('config_expires_after')
                ->defaultValue(3600)
            ->end()
        ->end();
};
