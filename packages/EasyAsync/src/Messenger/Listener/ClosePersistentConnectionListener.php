<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Messenger\Listener;

use EonX\EasyAsync\Doctrine\Closer\ConnectionCloser;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

final class ClosePersistentConnectionListener
{
    private bool $connectionClosed = false;

    private ?float $startIdleTime = null;

    /**
     * @param string[]|null $managers
     */
    public function __construct(
        private readonly ConnectionCloser $connectionCloser,
        private readonly float $maxIdleTime,
        private readonly ?array $managers = null,
    ) {
    }

    public function __invoke(WorkerRunningEvent $event): void
    {
        // If worker is processing messages then, reset state and skip
        if ($event->isWorkerIdle() === false) {
            $this->startIdleTime = null;
            $this->connectionClosed = false;

            return;
        }

        // If connection were closed already, skip
        if ($this->connectionClosed) {
            return;
        }

        $this->startIdleTime ??= \microtime(true);

        // Close connection, and update state not to keep calling this logic over and over
        if ((\microtime(true) - $this->startIdleTime) >= $this->maxIdleTime) {
            $this->connectionCloser->close($this->managers);
            $this->connectionClosed = true;
        }
    }
}
