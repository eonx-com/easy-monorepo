<?php
declare(strict_types=1);

use EonX\EasyWebhook\Signers\Rs256Signer;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('async')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('bus')->defaultValue('messenger.bus.default')->end()
                ->end()
            ->end()
            ->scalarNode('method')->defaultNull()->end()
            ->arrayNode('event')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('event_header')->defaultNull()->end()
                ->end()
            ->end()
            ->arrayNode('id')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                    ->scalarNode('id_header')->defaultNull()->end()
                ->end()
            ->end()
            ->arrayNode('signature')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultFalse()->end()
                    ->scalarNode('signature_header')->defaultNull()->end()
                    ->scalarNode('signer')->defaultValue(Rs256Signer::class)->end()
                    ->scalarNode('secret')->defaultNull()->end()
                ->end()
            ->end()
            ->booleanNode('use_default_middleware')->defaultTrue()->end()
        ->end();
};
