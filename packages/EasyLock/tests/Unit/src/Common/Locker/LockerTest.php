<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Common\Locker;

use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Tests\Stub\Factory\LockFactoryStub;
use EonX\EasyLock\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Lock\Store\InMemoryStore;

final class LockerTest extends AbstractUnitTestCase
{
    public function testCreateLockUsesGivenLockFactory(): void
    {
        $store = new InMemoryStore();
        $lockFactory = new LockFactoryStub($store);
        $locker = new Locker($lockFactory);

        $lock = $locker->createLock('some-resource');

        self::assertSame(1, $lockFactory->getCreateLockCallCount());
        self::assertTrue($lock->acquire());

        $lock->release();
    }
}
