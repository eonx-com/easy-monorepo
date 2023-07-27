<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Bridge\Laravel;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;

final class EasyLockServiceProviderTest extends AbstractLaravelTestCase
{
    public function testDispatchInLaravel(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(LockService::class, $app->get(LockServiceInterface::class));
    }
}
