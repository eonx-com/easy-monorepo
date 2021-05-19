<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\ChainSimpleDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\DoctrineOrmDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners\ResolveRequestAttributesListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Replace default data persister
    $services
        ->set('api_platform.data_persister', ChainSimpleDataPersister::class)
        ->arg('$persisters', tagged_iterator('api_platform.data_persister'));

    // Replace default doctrine orm data persister
    $services->set(DoctrineOrmDataPersisterInterface::class, DoctrineOrmDataPersister::class);

    $services->set(ResolveRequestAttributesListener::class);
};
