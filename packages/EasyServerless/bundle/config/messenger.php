<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AsyncAws\Sqs\SqsClient;
use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyServerless\Bundle\SqsHandler\SqsHandler;
use EonX\EasyServerless\Messenger\BusDriver\ReportBusDriver;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    // ReportBusDriver decorates the original BusDriver to report exceptions and dispatch an event
    $services->set(ReportBusDriver::class)
        ->decorate(BusDriver::class, priority: -1000)
        ->arg('$errorHandler', service(ErrorHandlerInterface::class)->nullOnInvalid())
        ->arg('$eventDispatcher', service(EventDispatcherInterface::class)->nullOnInvalid());

    $services->set(SqsClient::class);

    $services->set(SqsHandler::class)
        ->arg('$errorHandler', service(ErrorHandlerInterface::class)->nullOnInvalid())
        ->arg('$eventDispatcher', service(EventDispatcherInterface::class)->nullOnInvalid())
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->public();
};
