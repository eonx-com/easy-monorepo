<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('decorate_default_client')->defaultFalse()->end()
            ->booleanNode('decorate_easy_webhook_client')->defaultFalse()->end()
            ->booleanNode('decorate_messenger_sqs_client')->defaultFalse()->end()
            ->arrayNode('easy_bugsnag')
                ->canBeDisabled()
            ->end()
            ->arrayNode('modifiers')
                ->canBeDisabled()
                ->children()
                    ->arrayNode('whitelist')
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('psr_logger')
                ->canBeDisabled()
            ->end()
        ->end();
};
