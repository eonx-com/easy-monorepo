<?php

declare(strict_types=1);

namespace EonX\EasyLock\Tests\Bridge\Symfony;

use EonX\EasyLock\Interfaces\LockServiceInterface;
use EonX\EasyLock\LockService;

final class EasyLockBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testSanity
     */
    public static function providerTestSanity(): iterable
    {
        yield 'default config, no connection' => [null];

        yield 'in memory connection' => [[__DIR__ . '/Fixtures/config/in_memory_connection.yaml']];
    }

    /**
     * @param null|string[] $configs
     *
     * @dataProvider providerTestSanity
     */
    public function testSanity(?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        self::assertInstanceOf(LockService::class, $container->get(LockServiceInterface::class));
    }
}
