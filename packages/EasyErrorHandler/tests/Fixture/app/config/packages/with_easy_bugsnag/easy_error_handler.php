<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Monolog\Level;

return App::config([
    'easy_error_handler' => [
        'bugsnag' => [
            'threshold' => Level::Debug,
        ],
    ],
]);
