<?php

declare(strict_types=1);

use EonX\EasyPagination\Bridge\Symfony\Listeners\PaginationFromRequestListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(PaginationFromRequestListener::class)
        ->tag('kernel.event_listener');
};
