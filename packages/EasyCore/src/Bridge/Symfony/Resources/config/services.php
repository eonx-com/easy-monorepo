<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\Messenger\ProcessWithLockMiddleware;
use EonX\EasyCore\Lock\LockService;
use EonX\EasyCore\Lock\LockServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(LockServiceInterface::class, LockService::class);

    $services->set(ProcessWithLockMiddleware::class);
};
