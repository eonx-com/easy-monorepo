<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyServerless\Aws\HttpHandler\SymfonyHttpHandler;
use EonX\EasyServerless\Messenger\BusDriver\ReportBusDriver;
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

    // ReportBusDriver decorates the original BusDriver to report exceptions and dispatch an event
    $services->set(ReportBusDriver::class)
        ->decorate(BusDriver::class, priority: -1000)
        ->arg('$errorHandler', service(ErrorHandlerInterface::class)->nullOnInvalid())
        ->arg('$eventDispatcher', service(EventDispatcherInterface::class)->nullOnInvalid());
};
