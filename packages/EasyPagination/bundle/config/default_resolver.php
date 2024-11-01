<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyPagination\Listener\PaginationFromRequestListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // @todo Change priority to 10010 in 7.0 to allow other listeners in the middle
    $services
        ->set(PaginationFromRequestListener::class)
        ->tag('kernel.event_listener', [
            'event' => 'kernel.request',
            'priority' => 10000,
        ]);
};
