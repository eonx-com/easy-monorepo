<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'reset_services' => [
            'enabled' => false,
        ],
    ]);
};
