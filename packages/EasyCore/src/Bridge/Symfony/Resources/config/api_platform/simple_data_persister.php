<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\DoctrineOrmDataPersister;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces\DoctrineOrmDataPersisterInterface;
use EonX\EasyCore\Bridge\Symfony\ApiPlatform\Listeners\ResolveRequestAttributesListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure()
        ->autowire();

    // Replace default doctrine orm data persister
    $services->set(DoctrineOrmDataPersisterInterface::class, DoctrineOrmDataPersister::class);

    $services->set(ResolveRequestAttributesListener::class);
};
