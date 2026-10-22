<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('sensitive_data_sanitizer')
                ->canBeDisabled()
            ->end()
            ->arrayNode('bugsnag_handler')
                ->canBeEnabled()
                ->children()
                    ->integerNode('level')
                        ->defaultNull()
                        ->info('The log level to set on the Bugsnag handler, defaults to WARNING')
                    ->end()
                    ->arrayNode('channels')
                        ->info('Channels of the Bugsnag handler (the "app" channel if empty)')
                        ->stringPrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
