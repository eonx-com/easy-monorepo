<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBugsnag\Tests\Stub\Configurator\ErrorDetailsClientConfiguratorStub;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ErrorDetailsClientConfiguratorStub::class);
};
