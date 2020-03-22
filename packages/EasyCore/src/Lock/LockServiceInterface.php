<?php

declare(strict_types=1);

namespace EonX\EasyCore\Lock;

use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;

interface LockServiceInterface
{
    public function createLock(string $resource, ?float $ttl = null): LockInterface;

    public function setStore(PersistingStoreInterface $store): self;
}
