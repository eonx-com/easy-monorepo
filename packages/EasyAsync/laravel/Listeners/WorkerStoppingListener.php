<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel\Listeners;

use EonX\EasyAsync\Laravel\Exceptions\WorkerStoppingException;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Illuminate\Queue\Events\WorkerStopping;

final readonly class WorkerStoppingListener implements WorkerStoppingListenerInterface
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
    ) {
    }

    public function handle(WorkerStopping $event): void
    {
        $status = $event->status;

        if ($status === 0) {
            return;
        }

        $reason = self::REASONS[$event->status] ?? null;
        $exception = new WorkerStoppingException(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));

        $this->errorHandler->report($exception);
    }
}
