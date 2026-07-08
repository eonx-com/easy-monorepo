<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Laravel;

use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use ReflectionProperty;
use Symfony\Component\Lock\LockFactory;

final class EasyLockServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDispatchInLaravel(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(Locker::class, $app->get(LockerInterface::class));
    }

    public function testLockFactoryIsRegisteredAndSharedWithLocker(): void
    {
        $app = $this->getApp();

        $lockFactory = $app->get(LockFactory::class);
        $locker = $app->get(LockerInterface::class);

        self::assertInstanceOf(LockFactory::class, $lockFactory);
        self::assertSame($lockFactory, (new ReflectionProperty(Locker::class, 'lockFactory'))->getValue($locker));
    }
}
