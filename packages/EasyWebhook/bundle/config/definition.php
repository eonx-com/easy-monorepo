<?php
declare(strict_types=1);

use EonX\EasyWebhook\Common\Exception\InvalidSsrfProtectionConfigException;
use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Common\Signer\Rs256WebhookSigner;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
            ->arrayNode('ssrf_protection')
                ->canBeDisabled()
                ->validate()
                    ->always(static function (array $v): array {
                        // Disabled means allowed_ranges is inert, so don't fail the build on it
                        if ($v['enabled'] === false) {
                            return $v;
                        }

                        // Fail at container build on an allowed_ranges entry that would do nothing
                        try {
                            HttpClientFactory::validateAllowedRanges(
                                $v['extra_blocked_ranges'],
                                $v['allowed_ranges']
                            );
                        } catch (InvalidSsrfProtectionConfigException $exception) {
                            throw new InvalidConfigurationException($exception->getMessage(), previous: $exception);
                        }

                        return $v;
                    })
                ->end()
                ->children()
                    ->arrayNode('extra_blocked_ranges')
                        ->info(
                            'Additional CIDR ranges to reject as SSRF targets, on top of the standard '
                            . 'private + reserved defaults (which include the link-local 169.254.0.0/16 '
                            . 'used by cloud metadata endpoints). Checked against the resolved IP of every '
                            . 'request and redirect hop.'
                        )
                        ->defaultValue([])
                        ->stringPrototype()->end()
                    ->end()
                    ->arrayNode('allowed_ranges')
                        ->info(
                            'CIDR ranges to unblock by REMOVING a matching entry from the default block '
                            . 'list (e.g. "127.0.0.0/8" to reach IPv4 localhost). Each entry must match a '
                            . 'default range verbatim and must not be covered by another default (e.g. '
                            . '"::1/128" is inside "::/96"), otherwise it is rejected at startup. To reach '
                            . 'hosts this cannot express, use "enabled: false".'
                        )
                        ->defaultValue([])
                        ->stringPrototype()->end()
                    ->end()
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
