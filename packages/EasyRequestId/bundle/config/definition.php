<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('http_headers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->stringNode('correlation_id')
                        ->defaultValue('X-CORRELATION-ID')
                        ->info('Header used to resolve/send the correlation id from the HTTP request')
                    ->end()
                    ->stringNode('request_id')
                        ->defaultValue('X-REQUEST-ID')
                        ->info('Header used to resolve/send the request id from the HTTP request')
                    ->end()
                ->end()
            ->end()

            // Integrations
            ->arrayNode('easy_error_handler')
                ->canBeDisabled()
            ->end()
            ->arrayNode('easy_logging')
                ->canBeDisabled()
            ->end()
            ->arrayNode('easy_http_client')
                ->canBeDisabled()
            ->end()
            ->arrayNode('easy_webhook')
                ->canBeDisabled()
            ->end()
        ->end();
};
