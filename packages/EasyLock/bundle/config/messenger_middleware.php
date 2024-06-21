<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ProcessWithLockMiddleware::class)
        ->call('setLockService', [service(LockerInterface::class)]);
};
