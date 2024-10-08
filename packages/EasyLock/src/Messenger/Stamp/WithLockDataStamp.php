<?php
declare(strict_types=1);

namespace EonX\EasyLock\Messenger\Stamp;

use EonX\EasyLock\Common\ValueObject\LockData;
use EonX\EasyLock\Common\ValueObject\WithLockDataInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

final readonly class WithLockDataStamp implements StampInterface, WithLockDataInterface
{
    public function __construct(
        private string $resource,
        private ?float $ttl = null,
    ) {
    }

    public function getLockData(): LockData
    {
        return LockData::create($this->resource, $this->ttl);
    }
}
