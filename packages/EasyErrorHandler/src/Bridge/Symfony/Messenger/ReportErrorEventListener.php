<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Messenger;

use EonX\EasyErrorHandler\Exceptions\RetryableException;
use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

final readonly class ReportErrorEventListener
{
    public function __construct(
        private ErrorDetailsResolverInterface $errorDetailsResolver,
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $this->errorDetailsResolver->reset();
        $this->errorHandler->report(
            RetryableException::fromThrowable($event->getThrowable(), $event->willRetry())
        );
    }
}
