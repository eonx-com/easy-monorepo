<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\BusDriver;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyEventDispatcher\Dispatcher\EventDispatcherInterface;
use EonX\EasyServerless\Messenger\Event\EnvelopeDispatchedEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final readonly class ReportBusDriver implements BusDriver
{
    public function __construct(
        private BusDriver $decorated,
        private ?ErrorHandlerInterface $errorHandler = null,
        private ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function putEnvelopeOnBus(MessageBusInterface $bus, Envelope $envelope, string $transportName): void
    {
        try {
            $this->decorated->putEnvelopeOnBus($bus, $envelope, $transportName);
        } catch (Throwable $throwable) {
            $this->errorHandler?->report($throwable);

            throw $throwable;
        } finally {
            $this->eventDispatcher?->dispatch(new EnvelopeDispatchedEvent());
        }
    }
}

// TODO: Remove in v7.0
\class_alias(ReportBusDriver::class, '\EonX\EasyServerless\EasyErrorHandler\BusDriver\ReportBusDriver');
