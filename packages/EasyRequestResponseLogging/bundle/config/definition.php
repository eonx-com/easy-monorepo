<?php
declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->booleanNode('enabled')
                ->defaultFalse()
                ->info('Enable request/response logging')
            ->end()
            ->arrayNode('do_not_log_paths')
                ->info('Request paths (exact match with the path info) which are not logged')
                ->stringPrototype()->end()
                ->defaultValue(['/ping'])
            ->end()
            ->booleanNode('log_request_body')
                ->defaultTrue()
                ->info('Log the request body (only text content types are logged)')
            ->end()
            ->booleanNode('log_response_body')
                ->defaultTrue()
                ->info('Log the response body (only text content types are logged)')
            ->end()
            ->integerNode('max_body_size')
                ->defaultValue(32768)
                ->min(0)
                ->info('Max size in bytes of the logged body, a bigger sanitized body is truncated')
            ->end()
            ->integerNode('max_raw_body_size')
                ->defaultValue(1048576)
                ->min(0)
                ->info('Max size in bytes of the raw body, a bigger body is not logged at all')
            ->end()
        ->end();
};
