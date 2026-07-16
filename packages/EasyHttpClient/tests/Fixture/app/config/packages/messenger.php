<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'messenger' => [
            'transports' => [
                'sync' => [
                    'dsn' => 'sync://',
                ],
                'async' => [
                    'dsn' => 'in-memory://',
                ],
                'failed' => [
                    'dsn' => 'in-memory://',
                ],
            ],
        ],
    ],
]);
