<?php
declare(strict_types=1);

use EonX\EasyLogging\Interfaces\LoggerFactoryInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->scalarNode('default_channel')->defaultValue(LoggerFactoryInterface::DEFAULT_CHANNEL)->end()
            ->arrayNode('sensitive_data_sanitizer')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                ->end()
            ->end()
            ->booleanNode('stream_handler')->defaultTrue()->end()
            ->booleanNode('bugsnag_handler')->defaultFalse()->end()
            ->integerNode('stream_handler_level')->defaultNull()->end()
        ->end();
};
