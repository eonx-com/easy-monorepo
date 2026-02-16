<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_security', [
        'voters' => [
            'permission_voter' => true,
            'provider_voter' => true,
            'role_voter' => true,
        ],
    ]);

    $containerConfigurator->extension('easy_bugsnag', [
        'api_key' => 'api-key',
        'doctrine_dbal' => false,
        'sensitive_data_sanitizer' => false,
    ]);
};
