<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * @deprecated Since 2.4.31. Will be remove in 3.0. Use eonx-com/easy-lock package instead.
 */
interface LockServiceInterface
{
    public function createLock(string $resource, ?float $ttl = null): LockInterface;

    public function setStore(PersistingStoreInterface $store): self;
}
