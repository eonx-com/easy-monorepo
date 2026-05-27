<?php
declare(strict_types=1);

return [
    'app_metric' => [
        'enabled' => true,
        'namespace' => null,
    ],
    'health' => [
        'enabled' => true,
    ],
    'http' => [
        'enabled' => true,
        'lambda_timeout' => 30,
    ],
    'state' => [
        'check' => true,
    ],
];
