<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'snap_start' => [
            'enabled' => true,
            'warmers' => [
                'class_map' => [
                    'enabled' => false,
                ],
                'http_request' => [
                    'enabled' => false,
                ],
            ],
        ],
    ]);
};
