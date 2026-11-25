<?php
declare(strict_types=1);

return [
    'app_metric' => [
        'enabled' => true,
        'namespace' => null,
    ],
    'datadog' => [
        'enabled' => true,
    ],
    'health' => [
        'enabled' => true,
    ],
    'snap_start' => [
        'enabled' => false,
        'warmers' => [
            'class_map' => [
                'enabled' => true,
                'namespaces' => ['App\\'],
            ],
        ],
    ],
    'state' => [
        'check' => true,
    ],
];
