<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_bugsnag' => [
        'api_key' => '',
        'sensitive_data_sanitizer' => false,
        'doctrine_dbal' => false,
    ],
]);
