<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Bref Http Handler
    $services
        ->set(SymfonyHttpHandler::class)
        ->public(); // Must be public as Bref uses the PSR container to retrieve it
};
