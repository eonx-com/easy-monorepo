<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\Messenger;

use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class WithLockDataStamp implements StampInterface, WithLockDataInterface
{
    public function __construct(
        private string $resource,
        private ?float $ttl = null,
    ) {
    }

    public function getLockData(): LockDataInterface
    {
        return LockData::create($this->resource, $this->ttl);
    }
}
