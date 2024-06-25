<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyDoctrine\Bundle\Factory\ObjectCopierFactory;
use EonX\EasyDoctrine\EntityEvent\Copier\ObjectCopierInterface;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcher;
use EonX\EasyDoctrine\EntityEvent\Dispatcher\DeferredEntityEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(ObjectCopierInterface::class)
        ->factory([ObjectCopierFactory::class, 'create']);

    $services->set(DeferredEntityEventDispatcherInterface::class, DeferredEntityEventDispatcher::class);
};
