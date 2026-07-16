<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'test' => true,
        'uid' => [
            'default_uuid_version' => 7,
        ],
    ],
]);
