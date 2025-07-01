<?php
declare(strict_types=1);

namespace EonX\EasyServerless\EasyErrorHandler\BusDriver;

use Bref\Symfony\Messenger\Service\BusDriver;
use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final readonly class ReportBusDriver implements BusDriver
{
    public function __construct(
        private BusDriver $decorated,
        private ErrorHandlerInterface $errorHandler,
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
            $this->errorHandler->report($throwable);

            throw $throwable;
        }
    }
}
