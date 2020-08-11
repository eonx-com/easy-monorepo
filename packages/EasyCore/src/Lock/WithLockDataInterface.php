<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
interface WithLockDataInterface
{
    public function getLockData(): LockDataInterface;

    public function setResource(string $resource): void;

    public function setTtl(?float $ttl = null): void;
}
