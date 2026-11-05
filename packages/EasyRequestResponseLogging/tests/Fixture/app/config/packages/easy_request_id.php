<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if ($containerConfigurator->env() === 'test_easy_request_id_disabled') {
        return;
    }

    $containerConfigurator->extension('easy_request_id', [
        'easy_error_handler' => false,
        'easy_http_client' => false,
        'easy_logging' => false,
        'easy_webhook' => false,
    ]);
};
