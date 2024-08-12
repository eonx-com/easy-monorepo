<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasySecurity\Tests\Stub\Provider\RolesAndPermissionsProviderStub;
use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig, ContainerConfigurator $containerConfigurator): void {
    $easyBugsnagConfig->apiKey('api-key');

    $easyBugsnagConfig->doctrineDbal()
        ->enabled(false);

    $easyBugsnagConfig->sensitiveDataSanitizer()
        ->enabled(false);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RolesAndPermissionsProviderStub::class)
        ->arg('$roles', ['role'])
        ->arg('$permissions', ['permission']);
};
