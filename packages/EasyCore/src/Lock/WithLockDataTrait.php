<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
trait WithLockDataTrait
{
    /**
     * @var string
     */
    private $resource;

    /**
     * @var null|float
     */
    private $ttl;

    public function getLockData(): LockDataInterface
    {
        return new LockData($this->resource, $this->ttl);
    }

    public function setResource(string $resource): void
    {
        $this->resource = $resource;
    }

    public function setTtl(?float $ttl = null): void
    {
        $this->ttl = $ttl;
    }
}
