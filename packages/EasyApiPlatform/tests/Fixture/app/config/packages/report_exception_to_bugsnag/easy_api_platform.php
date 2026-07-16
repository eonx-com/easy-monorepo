<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_api_platform' => [
        'easy_error_handler' => [
            'report_exceptions_to_bugsnag' => true,
        ],
    ],
]);
