<?php

declare(strict_types=1);

use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersClearMiddleware;
use EonX\EasyAsync\Bridge\Symfony\Messenger\DoctrineManagersSanityCheckMiddleware;
use EonX\EasyAsync\Doctrine\ManagersClearer;
use EonX\EasyAsync\Doctrine\ManagersSanityChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default managers clearer
    $services->set(ManagersClearer::class);

    // Default managers sanity checker
    $services
        ->set(ManagersSanityChecker::class)
        ->arg('$logger', ref(LoggerInterface::class));

    // Default managers clearer middleware (clear all managers)
    $services->set(DoctrineManagersClearMiddleware::class);

    // Default managers sanity check middleware (check all managers)
    $services->set(DoctrineManagersSanityCheckMiddleware::class);
};
