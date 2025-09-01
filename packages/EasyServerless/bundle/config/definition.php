<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('assets_separate_domain')
                ->canBeDisabled()
                ->children()
                    ->stringNode('url')
                        ->defaultValue('%env(ASSETS_URL)%')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('easy_error_handler')
                ->canBeDisabled()
            ->end()
            ->arrayNode('monolog')
                ->canBeDisabled()
            ->end()
            ->arrayNode('state')
                ->canBeDisabled()
                ->children()
                    ->booleanNode('check')
                        ->defaultTrue()
                        ->info('Enable state check after each invocation.')
                    ->end()
                ->end()
        ->end();
};
