<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Messenger;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

final class ReportErrorEventListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        $this->errorHandler->report($event->getThrowable());
    }
}
