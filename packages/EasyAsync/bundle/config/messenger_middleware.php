<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Bundle\Enum\BundleParam;
use EonX\EasyAsync\Doctrine\Checker\ManagersSanityChecker;
use EonX\EasyAsync\Doctrine\Clearer\ManagersClearer;
use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersClearMiddleware;
use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersSanityCheckMiddleware;

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
        ->tag('monolog.logger', ['channel' => BundleParam::LogChannel->value]);

    // Default managers clearer middleware (clear all managers)
    $services->set(DoctrineManagersClearMiddleware::class);

    // Default managers sanity check middleware (check all managers)
    $services->set(DoctrineManagersSanityCheckMiddleware::class);
};
