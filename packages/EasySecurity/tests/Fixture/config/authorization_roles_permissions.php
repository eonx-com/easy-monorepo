<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySecurity\Tests\Stub\Provider\RolesAndPermissionsProviderStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_bugsnag', [
        'api_key' => 'api-key',
        'doctrine_dbal' => false,
        'sensitive_data_sanitizer' => false,
    ]);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RolesAndPermissionsProviderStub::class)
        ->arg('$roles', ['role'])
        ->arg('$permissions', ['permission']);
};
