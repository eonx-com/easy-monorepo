<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners\ResolveRequestAttributesListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('api_platform.data_persister', ChainSimpleDataPersister::class)
        ->args([ref('service_container'), ref('event_dispatcher'), null, tagged_iterator('api_platform.data_persister')]);

    $services->set(ResolveRequestAttributesListener::class)
        ->autoconfigure()
        ->args([ref('request_stack')]);
};
