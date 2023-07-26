<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\ApiPlatformConfig;

return static function (ApiPlatformConfig $apiPlatformConfig): void {
    $apiPlatformConfig
        ->title('Test API')
        ->pathSegmentNameGenerator('api_platform.path_segment_name_generator.dash')
        ->version('3.0.0');

    $apiPlatformConfig->collection()
        ->pagination()
        ->itemsPerPageParameterName('perPage')
        ->pageParameterName('page');

    $apiPlatformConfig->defaults()
        ->paginationClientItemsPerPage(true)
        ->paginationItemsPerPage(25)
        ->paginationMaximumItemsPerPage(50)
        ->stateless(true);

    $apiPlatformConfig->formats('json')
        ->mimeTypes(['application/json']);

    $apiPlatformConfig->formats('jsonld')
        ->mimeTypes(['application/ld+json']);

    $apiPlatformConfig->formats('jsonapi')
        ->mimeTypes(['application/vnd.api+json']);

    $apiPlatformConfig->formats('xml')
        ->mimeTypes(['application/xml', 'text/xml']);

    $apiPlatformConfig->formats('html')
        ->mimeTypes(['text/html']);

    $apiPlatformConfig->patchFormats('json')
        ->mimeTypes(['application/merge-patch+json']);

    $apiPlatformConfig->mapping()
        ->paths([
            \param('kernel.project_dir') . '/src/ApiResource/',
        ]);

    $apiPlatformConfig->swagger()
        ->apiKeys('Some_Authorization_Name')
        ->name('Authorization')
        ->type('header');

    $apiPlatformConfig->swagger()
        ->versions([3]);
};
