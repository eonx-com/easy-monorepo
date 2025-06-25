<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\State\Resetter\SymfonyServicesAppStateResetter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Bref Http Handler
    $services
        ->set(SymfonyHttpHandler::class)
        ->public(); // Must be public as Bref uses the PSR container to retrieve it

    // Registered in this main file so it always optimizes the Symfony services resetter
    $services->set(SymfonyServicesAppStateResetter::class);
};
