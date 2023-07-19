<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bridge\Symfony\DependencyInjection\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\Dispatchers\DeferredEntityEventDispatcherInterface;
use EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface;
use EonX\EasyDoctrine\Interfaces\ChangesetCleanerProviderInterface;
use EonX\EasyDoctrine\Interfaces\ObjectCopierInterface;
use EonX\EasyDoctrine\Providers\ChangesetCleanerProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ObjectCopierInterface::class)
        ->factory([ObjectCopierFactory::class, 'create']);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);

    $services
        ->instanceof(ChangesetCleanerInterface::class)
        ->tag('easy_doctrine.changeset_cleaner');
    $services->set(ChangesetCleanerProviderInterface::class, ChangesetCleanerProvider::class)
        ->arg('$cleaners', tagged_iterator('easy_doctrine.changeset_cleaner'));
};
