<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Stub\Factory;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class LockFactoryStub extends LockFactory
{
    private int $createLockCallCount = 0;

    public function createLock(string $resource, ?float $ttl = 300.0, bool $autoRelease = true): SharedLockInterface
    {
        ++$this->createLockCallCount;

        return parent::createLock($resource, $ttl, $autoRelease);
    }

    public function getCreateLockCallCount(): int
    {
        return $this->createLockCallCount;
    }
}
