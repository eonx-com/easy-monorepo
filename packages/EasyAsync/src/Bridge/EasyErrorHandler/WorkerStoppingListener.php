<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\EasyErrorHandler;

use EonX\EasyAsync\Bridge\Laravel\Interfaces\QueueWorkerStoppingReasonsInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Illuminate\Queue\Events\WorkerStopping;

final class WorkerStoppingListener
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function handle(WorkerStopping $event): void
    {
        $status = $event->status;

        if ($status === 0) {
            return;
        }

        $reason = QueueWorkerStoppingReasonsInterface::REASONS[$event->status] ?? null;
        $exception = new WorkerStoppingException(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));

        $this->errorHandler->report($exception);
    }
}
