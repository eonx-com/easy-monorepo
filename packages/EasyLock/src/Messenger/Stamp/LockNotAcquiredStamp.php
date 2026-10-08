<?php
declare(strict_types=1);

namespace EonX\EasyLock\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * Added by ProcessWithLockMiddleware to the returned envelope when the message was not handled
 * because its lock was already acquired by another process.
 */
final readonly class LockNotAcquiredStamp implements NonSendableStampInterface
{
    public function __construct(
        private string $resource,
        private float $ttl,
    ) {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getTtl(): float
    {
        return $this->ttl;
    }
}
