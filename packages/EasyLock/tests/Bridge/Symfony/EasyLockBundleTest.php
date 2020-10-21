<?php

declare(strict_types=1);

namespace EonX\EasyLock\Tests\Bridge\Symfony;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;

final class EasyLockBundleTest extends AbstractSymfonyTestCase
{
    public function testSanity(): void
    {
        $container = $this->getKernel([__DIR__ . '/Fixtures/config/in_memory_connection.yaml'])->getContainer();

        self::assertInstanceOf(LockService::class, $container->get(LockServiceInterface::class));
    }
}
