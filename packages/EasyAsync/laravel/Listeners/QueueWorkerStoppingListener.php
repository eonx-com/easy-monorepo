<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel\Listeners;

use Illuminate\Queue\Events\WorkerStopping;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class QueueWorkerStoppingListener implements WorkerStoppingListenerInterface
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function handle(WorkerStopping $event): void
    {
        $reason = self::REASONS[$event->status] ?? null;

        $this->logger->warning(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));
    }
}
