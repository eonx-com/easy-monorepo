<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_bugsnag' => [
        'sensitive_data_sanitizer' => false,
    ],
]);
