<?php

declare(strict_types=1);

namespace EonX\EasyLock\Interfaces;

use Closure;
use Symfony\Component\Lock\LockInterface;

interface LockServiceInterface
{
    public function createLock(string $resource, ?float $ttl = null): LockInterface;

    public function processWithLock(LockDataInterface $lockData, Closure $func): mixed;
}
