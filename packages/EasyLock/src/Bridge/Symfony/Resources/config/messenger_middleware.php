<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyLock\Bridge\Symfony\Messenger\ProcessWithLockMiddleware;
use EonX\EasyLock\Interfaces\LockServiceInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set(ProcessWithLockMiddleware::class)
        ->call('setLockService', [service(LockServiceInterface::class)]);
};
