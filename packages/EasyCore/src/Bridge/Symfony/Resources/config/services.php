<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;
use EonX\EasyCore\Bridge\Symfony\Messenger\ProcessWithLockMiddleware;
use EonX\EasyCore\Bridge\Symfony\Messenger\StopWorkerOnEmClosedEventListener;
use EonX\EasyCore\Lock\LockService;
use EonX\EasyCore\Lock\LockServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Messenger
    $services
        ->set(StopWorkerOnEmClosedEventListener::class)
        ->tag('kernel.event_listener');

    $services->set(ForBuildEnvVarProcessor::class);

    $services->set(LockServiceInterface::class, LockService::class);

    $services->set(ProcessWithLockMiddleware::class);
};
