<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Messenger;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

final class ReportErrorEventListener
{
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorHandlerInterface $errorHandler
    ) {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $this->errorDetailsResolver->reset();
        $this->errorHandler->report($event->getThrowable());
    }
}
