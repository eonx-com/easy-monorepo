<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_logging', [
        'use_symfony_monolog_bundle' => true,
        'sensitive_data_sanitizer' => [
            'enabled' => true,
        ],
    ]);

    $containerConfigurator->extension('monolog', [
        'handlers' => [
            'main' => [
                'type' => 'test',
            ],
        ],
    ]);
};
