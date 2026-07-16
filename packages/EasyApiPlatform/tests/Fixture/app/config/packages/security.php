<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'security' => [
        'firewalls' => [
            'main' => [
                'pattern' => '^/',
                'security' => false,
            ],
        ],
    ],
]);
