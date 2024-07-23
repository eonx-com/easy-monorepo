<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\ApiPlatformConfig;

return static function (ApiPlatformConfig $apiPlatformConfig): void {
    $apiPlatformConfig
        ->pathSegmentNameGenerator('api_platform.path_segment_name_generator.dash');

    $apiPlatformConfig->mapping()
        ->paths([
            param('kernel.project_dir') . '/src/ApiResource/',
        ]);
};
