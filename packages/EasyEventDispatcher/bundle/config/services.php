<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyEventDispatcher\Dispatcher\EventDispatcher;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
        ->set(EventDispatcherInterface::class, EventDispatcher::class)
        ->args([service(SymfonyEventDispatcherInterface::class)]);
};