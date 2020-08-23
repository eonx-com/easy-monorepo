<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\Symfony\Messenger;

use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class WithLockDataStamp implements StampInterface, WithLockDataInterface
{
    /**
     * @var string
     */
    private $resource;

    /**
     * @var null|float
     */
    private $ttl;

    public function __construct(string $resource, ?float $ttl = null)
    {
        $this->resource = $resource;
        $this->ttl = $ttl;
    }

    public function getLockData(): LockDataInterface
    {
        return LockData::create($this->resource, $this->ttl);
    }
}
