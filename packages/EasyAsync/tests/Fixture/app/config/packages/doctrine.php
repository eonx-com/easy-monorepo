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
        ],
    ],
]);
