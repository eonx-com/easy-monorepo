<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_request_response_logging', [
        'enabled' => true,
        'max_body_size' => 1024,
        'max_raw_body_size' => 4096,
    ]);
};
