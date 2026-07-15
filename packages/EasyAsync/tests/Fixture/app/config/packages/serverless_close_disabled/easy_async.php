<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_async', [
        'doctrine' => [
            'close_persistent_connections' => [
                'enabled' => false,
            ],
        ],
    ]);
};
