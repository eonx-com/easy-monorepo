<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Messenger\Listener;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyErrorHandler\Common\Resolver\ErrorDetailsResolverInterface;
use EonX\EasyServerless\Event\ServerlessWorkerMessageFailedEvent;

final readonly class ServerlessWorkerMessageFailedListener
{
    public function __construct(
        private ErrorDetailsResolverInterface $errorDetailsResolver,
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function __invoke(ServerlessWorkerMessageFailedEvent $event): void
    {
        $this->errorDetailsResolver->reset();
        $this->errorHandler->report(
            RetryableException::fromThrowable($event->getThrowable(), $event->willRetry())
        );
    }
}
