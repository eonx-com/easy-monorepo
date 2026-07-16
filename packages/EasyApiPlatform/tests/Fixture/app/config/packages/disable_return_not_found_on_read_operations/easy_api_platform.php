<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_api_platform' => [
        'return_not_found_on_read_operations' => false,
    ],
]);
