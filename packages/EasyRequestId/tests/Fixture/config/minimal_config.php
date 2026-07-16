<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_request_id', [
        'easy_error_handler' => false,
        'easy_logging' => false,
        'easy_http_client' => false,
        'easy_webhook' => false,
    ]);
};
