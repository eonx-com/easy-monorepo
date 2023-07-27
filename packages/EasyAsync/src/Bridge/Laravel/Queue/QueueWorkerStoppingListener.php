<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Queue;

use EonX\EasyAsync\Bridge\Laravel\Interfaces\QueueWorkerStoppingReasonsInterface;
use Illuminate\Queue\Events\WorkerStopping;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class QueueWorkerStoppingListener
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function handle(WorkerStopping $event): void
    {
        $reason = QueueWorkerStoppingReasonsInterface::REASONS[$event->status] ?? null;

        $this->logger->warning(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));
    }
}
