<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('is_debug')->defaultValue('%env(bool:APP_DEBUG)%')->end()
            ->booleanNode('use_twig')->defaultTrue()->end()
        ->end();
};
