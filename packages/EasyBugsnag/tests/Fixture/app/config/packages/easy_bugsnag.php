<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_bugsnag' => [
        'api_key' => 'my-bugsnag-api-key',
        'sensitive_data_sanitizer' => true,
        'session_tracking' => [
            'exclude_urls' => ['^/ping'],
        ],
    ],
]);
