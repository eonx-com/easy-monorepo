<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Bridge\Symfony\Listeners\PaginationFromRequestListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(PaginationFromRequestListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'priority' => 10000]);
};
