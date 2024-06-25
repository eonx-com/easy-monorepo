<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Laravel;

use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;

final class EasyLockServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDispatchInLaravel(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(Locker::class, $app->get(LockerInterface::class));
    }
}
