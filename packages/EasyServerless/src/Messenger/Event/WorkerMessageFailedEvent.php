<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Messenger\Event;

use Symfony\Component\Messenger\Envelope;
use Throwable;

final class WorkerMessageFailedEvent extends AbstractWorkerMessageEvent
{
    private bool $willRetry = false;

    public function __construct(
        Envelope $envelope,
        string $receiverName,
        private readonly Throwable $throwable,
    ) {
        parent::__construct($envelope, $receiverName);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    public function setForRetry(): void
    {
        $this->willRetry = true;
    }

    public function willRetry(): bool
    {
        return $this->willRetry;
    }
}
