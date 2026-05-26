<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Bundle\Listener\SecurityHeaderResponseListener;
use EonX\EasyServerless\SecurityHeader\Hydrator\SecurityHeadersHydrator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SecurityHeadersHydrator::class);

    $services->set(SecurityHeaderResponseListener::class);
};
