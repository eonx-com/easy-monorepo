<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\Bundle\Listener\TrustApiGatewayProxyRequestListener;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Bref Http Handler
    $services
        ->set(SymfonyHttpHandler::class)
        ->public(); // Must be public as Bref uses the PSR container to retrieve it

    // Trusted-proxy policy for the Lambda + API Gateway context. Registered via #[AsEventListener];
    // a no-op outside a remote Lambda
    $services->set(TrustApiGatewayProxyRequestListener::class);
};
