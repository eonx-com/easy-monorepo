<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use InvalidArgumentException;

return App::config([
    'easy_error_handler' => [
        'logger' => [
            'exception_log_levels' => [
                InvalidArgumentException::class => 200,
            ],
        ],
    ],
]);
