<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'easy_http_client' => [
        'decorate_default_client' => true,
        'decorate_easy_webhook_client' => true,
        'easy_bugsnag' => false,
        'psr_logger' => false,
    ],
]);
