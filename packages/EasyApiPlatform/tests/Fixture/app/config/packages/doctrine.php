<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'doctrine' => [
        'dbal' => [
            'connections' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'url' => 'sqlite:///:memory:',
                ],
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'entity_managers' => [
                'default' => [
                    'mappings' => [
                        'AppAdvancedSearchFilterApiResource' => [
                            'dir' => param('kernel.project_dir') . '/src/AdvancedSearchFilter/ApiResource',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource',
                            'type' => 'attribute',
                        ],
                        'AppCustomPaginatorApiResource' => [
                            'dir' => param('kernel.project_dir') . '/src/CustomPaginator/ApiResource',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyApiPlatform\Tests\Fixture\App\CustomPaginator\ApiResource',
                            'type' => 'attribute',
                        ],
                        'AppReturnNotFoundOnReadOperationApiResource' => [
                            'dir' => param('kernel.project_dir') . '/src/ReturnNotFoundOnReadOperation/ApiResource',
                            'is_bundle' => false,
                            'prefix' => 'EonX\EasyApiPlatform\Tests\Fixture\App\ReturnNotFoundOnReadOperation\ApiResource',
                            'type' => 'attribute',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
