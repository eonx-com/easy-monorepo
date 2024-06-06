<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Listener;

use EonX\EasyAsync\Doctrine\Closer\ManagersCloser;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

final class ClosePersistentConnectionListener
{
    private bool $managersClosed = false;

    private ?float $startIdleTime = null;

    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private readonly ManagersCloser $managersCloser,
        private readonly float $maxIdleTime,
        private readonly ?array $managers = null,
    ) {
    }

    public function __invoke(WorkerRunningEvent $event): void
    {
        // If worker is processing messages then, reset state and skip
        if ($event->isWorkerIdle() === false) {
            $this->startIdleTime = null;
            $this->managersClosed = false;

            return;
        }

        // If manager were closed already, skip
        if ($this->managersClosed) {
            return;
        }

        $this->startIdleTime ??= \microtime(true);

        // Close managers, and update state not to keep calling this logic over and over
        if ((\microtime(true) - $this->startIdleTime) >= $this->maxIdleTime) {
            $this->managersCloser->close($this->managers);
            $this->managersClosed = true;
        }
    }
}
