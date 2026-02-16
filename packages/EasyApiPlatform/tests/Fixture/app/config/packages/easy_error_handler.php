<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_error_handler' => [
        'translation_domain' => 'violations',
        'response' => [
            'code' => 'custom_code',
            'exception' => 'custom_exception',
            'message' => 'custom_message',
            'sub_code' => 'custom_sub_code',
            'time' => 'custom_time',
            'violations' => 'custom_violations',
            'extended_exception_keys' => [
                'class' => 'custom_class',
                'file' => 'custom_file',
                'line' => 'custom_line',
                'message' => 'custom_message',
                'trace' => 'custom_trace',
            ],
        ],
    ],
]);
