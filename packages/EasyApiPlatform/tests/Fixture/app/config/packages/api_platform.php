<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'api_platform' => [
        'title' => 'Test API',
        'path_segment_name_generator' => 'api_platform.path_segment_name_generator.dash',
        'version' => '3.0.0',
        'collection' => [
            'pagination' => [
                'items_per_page_parameter_name' => 'perPage',
                'page_parameter_name' => 'page',
            ],
        ],
        'defaults' => [
            'pagination_client_items_per_page' => true,
            'pagination_items_per_page' => 25,
            'pagination_maximum_items_per_page' => 50,
            'stateless' => true,
        ],
        'formats' => [
            'json' => [
                'mime_types' => ['application/json'],
            ],
            'jsonld' => [
                'mime_types' => ['application/ld+json'],
            ],
            'jsonapi' => [
                'mime_types' => ['application/vnd.api+json'],
            ],
            'xml' => [
                'mime_types' => ['application/xml', 'text/xml'],
            ],
            'html' => [
                'mime_types' => ['text/html'],
            ],
        ],
        'patch_formats' => [
            'json' => [
                'mime_types' => ['application/merge-patch+json'],
            ],
        ],
        'error_formats' => [
            'json' => [
                'mime_types' => ['application/json'],
            ],
        ],
        'serializer' => [
            'hydra_prefix' => true,
        ],
        'mapping' => [
            'paths' => [
                param('kernel.project_dir') . '/src/AdvancedSearchFilter/ApiResource/',
                param('kernel.project_dir') . '/src/CustomPaginator/ApiResource/',
                param('kernel.project_dir') . '/src/EasyErrorHandler/ApiResource/',
                param('kernel.project_dir') . '/src/OpenApi/ApiResource/',
                param('kernel.project_dir') . '/src/ReturnNotFoundOnReadOperation/ApiResource/',
            ],
        ],
        'swagger' => [
            'api_keys' => [
                'Some_Authorization_Name' => [
                    'name' => 'Authorization',
                    'type' => 'header',
                ],
            ],
            'versions' => [3],
        ],
    ],
]);
