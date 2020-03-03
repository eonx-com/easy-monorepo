<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Listeners;

use Illuminate\Queue\Events\WorkerStopping;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class QueueWorkerStoppingListener
{
    /**
     * @var mixed[]
     */
    private static $reasons = [
        12 => 'Memory exceeded'
    ];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * QueueWorkerStoppingListener constructor.
     *
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Output worker stopping event with status.
     *
     * @param \Illuminate\Queue\Events\WorkerStopping $event
     *
     * @return void
     */
    public function handle(WorkerStopping $event): void
    {
        $reason = static::$reasons[$event->status] ?? null;

        $this->logger->warning(\sprintf(
            'Worker stopping with status "%s"%s',
            $event->status,
            $reason ? \sprintf(' (%s)', $reason) : ''
        ));
    }
}
