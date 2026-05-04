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
                    ->stringNode('bus')->defaultValue('messenger.bus.default')->end()
                ->end()
            ->end()
            ->stringNode('method')->defaultNull()->end()
            ->arrayNode('event')
                ->canBeDisabled()
                ->children()
                    ->stringNode('header')->defaultValue('X-Webhook-Event')->end()
                ->end()
            ->end()
            ->arrayNode('id')
                ->canBeDisabled()
                ->children()
                    ->stringNode('header')->defaultValue('X-Webhook-Id')->end()
                ->end()
            ->end()
            ->arrayNode('signature')
                ->canBeEnabled()
                ->children()
                    ->stringNode('header')->defaultNull()->end()
                    ->stringNode('signer')->defaultValue(Rs256WebhookSigner::class)->end()
                    ->stringNode('secret')->defaultNull()->end()
                ->end()
            ->end()
            ->booleanNode('use_default_middleware')->defaultTrue()->end()
        ->end();
};
