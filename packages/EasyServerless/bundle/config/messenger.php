<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AsyncAws\Sqs\SqsClient;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyServerless\Bundle\SqsHandler\SqsHandler;
use EonX\EasyServerless\Datadog\Tracer\EnvelopeTracer;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(SqsClient::class);

    $services->set(SqsHandler::class)
        ->arg('$logger', service(LoggerInterface::class)->nullOnInvalid())
        ->arg('$errorHandler', service(ErrorHandlerInterface::class)->nullOnInvalid())
        ->arg('$eventDispatcher', service(EventDispatcherInterface::class)->nullOnInvalid())
        ->arg('$symfonyEventDispatcher', service(SymfonyEventDispatcherInterface::class)->nullOnInvalid())
        ->arg('$envelopeTracer', service(EnvelopeTracer::class)->nullOnInvalid())
        ->public();
};
