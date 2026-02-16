<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use stdClass;

return App::config([
    'easy_doctrine' => [
        'easy_error_handler' => false,
        'deferred_dispatcher_entities' => [
            stdClass::class,
        ],
    ],
]);
