<?php

declare(strict_types=1);

use EonX\EasyCore\Bridge\Symfony\Env\ForBuildEnvVarProcessor;
use EonX\EasyCore\Bridge\Symfony\Messenger\ProcessWithLockMiddleware;
use EonX\EasyCore\Lock\LockService;
use EonX\EasyCore\Lock\LockServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind(
            Doctrine\ORM\EntityManagerInterface::class,
            expr('@=service("EonX\EasyCore\Bridge\Symfony\Doctrine\EntityManagerResolver").getManager()')
        );

    $services->set(ForBuildEnvVarProcessor::class);

    $services->set(LockServiceInterface::class, LockService::class);

    $services->set(ProcessWithLockMiddleware::class);
};
