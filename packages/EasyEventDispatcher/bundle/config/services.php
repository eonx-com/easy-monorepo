<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcher;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(EventDispatcherInterface::class, EventDispatcher::class)
        ->arg('$eventDispatcher', service(SymfonyEventDispatcherInterface::class));
};
