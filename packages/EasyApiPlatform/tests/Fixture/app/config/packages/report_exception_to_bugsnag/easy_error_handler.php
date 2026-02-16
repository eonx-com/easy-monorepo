<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_error_handler' => [
        'bugsnag' => [
            'ignored_exceptions' => [],
        ],
    ],
]);
