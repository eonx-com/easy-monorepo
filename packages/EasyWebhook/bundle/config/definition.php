<?php
declare(strict_types=1);

use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('async')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('bus')->defaultValue('messenger.bus.default')->end()
                ->end()
            ->end()
            ->scalarNode('method')->defaultNull()->end()
            ->arrayNode('event')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('header')->defaultValue('X-Webhook-Event')->end()
                ->end()
            ->end()
            ->arrayNode('id')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('header')->defaultValue('X-Webhook-Id')->end()
                ->end()
            ->end()
            ->arrayNode('signature')
                ->canBeEnabled()
                ->children()
                    ->scalarNode('header')->defaultNull()->end()
                    ->scalarNode('signer')->defaultValue(Rs256WebhookSigner::class)->end()
                    ->scalarNode('secret')->defaultNull()->end()
                ->end()
            ->end()
            ->booleanNode('use_default_middleware')->defaultTrue()->end()
        ->end();
};
