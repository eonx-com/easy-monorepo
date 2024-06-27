<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Messenger\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

final class WorkerMessageFailedListener
{
    public function __construct(
        private readonly ErrorDetailsResolverInterface $errorDetailsResolver,
        private readonly ErrorHandlerInterface $errorHandler,
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
