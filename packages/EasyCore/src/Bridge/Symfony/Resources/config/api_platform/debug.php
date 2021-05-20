<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector\RequestDataCollector;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\TraceableChainSimpleDataPersister;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RequestDataCollector::class)
        ->decorate('api_platform.data_collector.request')
        ->args(
            [ref('EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataCollector\RequestDataCollector.inner'), ref(
                'api_platform.data_persister'
            )]
        );
};
