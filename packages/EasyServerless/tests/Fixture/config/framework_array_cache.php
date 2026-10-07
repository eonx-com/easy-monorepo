<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->alias('cache.app_alias', 'cache.app');

    $containerConfigurator->extension('framework', [
        'cache' => [
            'app' => 'cache.adapter.array',
            'system' => 'cache.adapter.array',
            'pools' => [
                'cache.app_alias_child' => [
                    'adapter' => 'cache.app_alias',
                    'public' => true,
                ],
                'cache.app_child' => [
                    'adapter' => 'cache.app',
                    'public' => true,
                ],
                'cache.app_grandchild' => [
                    'adapter' => 'cache.app_child',
                    'public' => true,
                ],
                'cache.chain' => [
                    'adapters' => ['cache.app', 'cache.adapter.array'],
                    'public' => true,
                ],
                'cache.standalone' => [
                    'adapter' => 'cache.adapter.array',
                    'public' => true,
                ],
                'cache.system_child' => [
                    'adapter' => 'cache.system',
                    'public' => true,
                ],
                'cache.system_grandchild' => [
                    'adapter' => 'cache.system_child',
                    'public' => true,
                ],
            ],
        ],
        // The tests do not use the Serializer. When it is enabled, the container fails to compile in the monorepo,
        // where the Validator component is installed but not enabled: the "argument_resolver.request_payload" service
        // then references the missing "validator.translation_domain" parameter
        'serializer' => [
            'enabled' => false,
        ],
    ]);
};
