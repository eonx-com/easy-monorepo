<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'snap_start' => [
            'enabled' => true,
            'warmers' => [
                'class_map' => [
                    'namespaces' => ['App\\Api\\', 'App\\Domain\\'],
                ],
                'http_request' => [
                    'host' => 'api.example.com',
                    'path' => '/ping',
                ],
            ],
        ],
    ]);
};
