<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyAsync\Messenger\Middleware\DoctrineManagersClearMiddleware;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Default managers clearer middleware (clear all managers)
    $services->set(DoctrineManagersClearMiddleware::class);
};
