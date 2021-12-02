<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector\RequestDataCollector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RequestDataCollector::class)
        ->decorate('api_platform.data_collector.request')
        ->args([
            service('EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector\RequestDataCollector.inner'),
            service('api_platform.data_persister'),
        ]);
};
