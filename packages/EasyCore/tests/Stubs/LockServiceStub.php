<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Stubs;

use EonX\EasyCore\Lock\LockServiceInterface;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\PersistingStoreInterface;

final class LockServiceStub implements LockServiceInterface
{
    /**
     * @var \EonX\EasyCore\Tests\Stubs\LockStub
     */
    private $lock;

    public function __construct(LockStub $lock)
    {
        $this->lock = $lock;
    }

    public function createLock(string $resource, ?float $ttl = null): LockInterface
    {
        return $this->lock;
    }

    public function setStore(PersistingStoreInterface $store): LockServiceInterface
    {
        return $this;
    }
}
