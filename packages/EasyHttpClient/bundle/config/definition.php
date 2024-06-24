<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->booleanNode('decorate_default_client')->defaultFalse()->end()
            ->booleanNode('decorate_easy_webhook_client')->defaultFalse()->end()
            ->booleanNode('decorate_messenger_sqs_client')->defaultFalse()->end()
            ->booleanNode('easy_bugsnag_enabled')->defaultTrue()->end()
            ->arrayNode('modifiers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')
                        ->defaultTrue()
                        ->info('Enables request_data_modifiers globally, null defaults to true.')
                    ->end()
                    ->arrayNode('whitelist')
                        ->beforeNormalization()->castToArray()->end()
                        ->defaultValue([null])
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->booleanNode('psr_logger_enabled')->defaultTrue()->end()
        ->end();
};
