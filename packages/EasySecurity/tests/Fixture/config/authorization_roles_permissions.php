<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySecurity\Tests\Stub\Provider\RolesAndPermissionsProviderStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RolesAndPermissionsProviderStub::class)
        ->arg('$roles', ['role'])
        ->arg('$permissions', ['permission']);
};
