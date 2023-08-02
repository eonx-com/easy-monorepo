<?php
declare(strict_types=1);

use EonX\EasyRequestId\Interfaces\RequestIdServiceInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $definition->rootNode()
        ->children()
            ->arrayNode('http_headers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('correlation_id')
                        ->defaultValue(RequestIdServiceInterface::DEFAULT_HTTP_HEADER_CORRELATION_ID)
                        ->info('Header used to resolve/send the correlation id from the HTTP request')
                    ->end()
                    ->scalarNode('request_id')
                        ->defaultValue(RequestIdServiceInterface::DEFAULT_HTTP_HEADER_REQUEST_ID)
                        ->info('Header used to resolve/send the request id from the HTTP request')
                    ->end()
                ->end()
            ->end()

            // Bridges
            ->booleanNode('easy_error_handler')->defaultTrue()->end()
            ->booleanNode('easy_logging')->defaultTrue()->end()
            ->booleanNode('easy_http_client')->defaultTrue()->end()
            ->booleanNode('easy_webhook')->defaultTrue()->end()
        ->end();
};
