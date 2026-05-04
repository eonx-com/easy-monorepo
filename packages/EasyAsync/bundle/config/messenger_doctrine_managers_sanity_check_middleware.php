<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersSanityCheckMiddleware;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default managers sanity check middleware (check all managers)
    $services->set(DoctrineManagersSanityCheckMiddleware::class);
};
