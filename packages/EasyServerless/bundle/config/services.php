<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\Bundle\Enum\ConfigParam;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // Bref Http Handler
    $services
        ->set(SymfonyHttpHandler::class)
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->arg('$lambdaTimeoutSeconds', param(ConfigParam::HttpLambdaTimeout->value))
        ->public(); // Must be public as Bref uses the PSR container to retrieve it
};
