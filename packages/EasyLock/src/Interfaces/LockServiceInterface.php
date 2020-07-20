<?php

declare(strict_types=1);

namespace EonX\EasyLock\Interfaces;

use Symfony\Component\Lock\LockInterface;

interface LockServiceInterface
{
    public function createLock(string $resource, ?float $ttl = null): LockInterface;
}
