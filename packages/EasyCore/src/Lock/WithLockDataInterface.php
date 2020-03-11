<?php
declare(strict_types=1);

namespace EonX\EasyCore\Lock;

interface WithLockDataInterface
{
    public function getLockData(): LockDataInterface;

    public function setResource(string $resource): void;

    public function setTtl(?float $ttl = null): void;
}
