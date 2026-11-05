<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_request_response_logging', [
        'enabled' => true,
        'log_request_body' => false,
        'log_response_body' => false,
    ]);
};
