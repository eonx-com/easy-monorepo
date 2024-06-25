<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Bundle;

use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyLockBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testSanity
     */
    public static function providerTestSanity(): iterable
    {
        yield 'default config, no connection' => [null];

        yield 'in memory connection' => [[__DIR__ . '/../../Fixture/config/in_memory_connection.php']];
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('providerTestSanity')]
    public function testSanity(?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        self::assertInstanceOf(Locker::class, $container->get(LockerInterface::class));
    }
}
