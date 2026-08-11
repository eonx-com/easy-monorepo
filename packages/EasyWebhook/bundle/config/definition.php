<?php
declare(strict_types=1);

use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
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
            ->arrayNode('request_limits')
                ->canBeEnabled()
                ->info('Guards against DoS from a slow or oversized webhook response.')
                ->children()
                    ->integerNode('timeout')
                        ->defaultValue(HttpClientFactory::DEFAULT_TIMEOUT)
                        ->min(0)
                        ->info(
                            'Idle timeout in seconds - abort when the target stalls. '
                            . '0 keeps PHP default_socket_timeout.'
                        )
                    ->end()
                    ->integerNode('max_duration')
                        ->defaultValue(HttpClientFactory::DEFAULT_MAX_DURATION)
                        ->min(0)
                        ->info(
                            'Total request duration cap in seconds - guards against a slow/trickling '
                            . 'target holding a worker open. 0 = unlimited.'
                        )
                    ->end()
                    ->integerNode('max_response_bytes')
                        ->defaultValue(HttpClientFactory::DEFAULT_MAX_RESPONSE_BYTES)
                        ->min(0)
                        ->info(
                            'Maximum response body size in bytes before the transfer is aborted, '
                            . 'guarding against memory/storage exhaustion. 0 = unlimited.'
                        )
                    ->end()
                ->end()
            ->end()
            ->booleanNode('use_default_middleware')->defaultTrue()->end()
        ->end();
};
