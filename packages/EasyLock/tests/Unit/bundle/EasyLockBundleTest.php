<?php
declare(strict_types=1);

namespace EonX\EasyLock\Tests\Unit\Bundle;

use EonX\EasyLock\Bundle\Enum\ConfigServiceId;
use EonX\EasyLock\Common\Locker\Locker;
use EonX\EasyLock\Common\Locker\LockerInterface;
use EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LogLevel;
use ReflectionProperty;
use Symfony\Component\Lock\LockFactory;

final class EasyLockBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testMessengerMiddlewareLockNotAcquiredLogLevel
     */
    public static function provideMessengerMiddlewareLockNotAcquiredLogLevelData(): iterable
    {
        yield 'default config' => [null, LogLevel::WARNING];

        yield 'custom log level' => [
            [__DIR__ . '/../../Fixture/config/lock_not_acquired_log_level_error.php'],
            LogLevel::ERROR,
        ];
    }

    /**
     * @see testSanity
     */
    public static function provideSanityData(): iterable
    {
        yield 'default config, no connection' => [null];

        yield 'in memory connection' => [[__DIR__ . '/../../Fixture/config/in_memory_connection.php']];
    }

    public function testLockFactoryIsRegisteredAndSharedWithLocker(): void
    {
        $container = $this->getKernel()
            ->getContainer();

        $lockFactory = $container->get(ConfigServiceId::LockFactory->value);
        $locker = $container->get(LockerInterface::class);

        self::assertInstanceOf(LockFactory::class, $lockFactory);
        self::assertSame($lockFactory, (new ReflectionProperty(Locker::class, 'lockFactory'))->getValue($locker));
        self::assertSame(
            $container->get(ConfigServiceId::Store->value),
            (new ReflectionProperty(LockFactory::class, 'store'))->getValue($lockFactory)
        );
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('provideMessengerMiddlewareLockNotAcquiredLogLevelData')]
    public function testMessengerMiddlewareLockNotAcquiredLogLevel(?array $configs, string $expectedLogLevel): void
    {
        $middleware = $this->getKernel($configs)
            ->getContainer()
            ->get(ProcessWithLockMiddleware::class);

        self::assertInstanceOf(ProcessWithLockMiddleware::class, $middleware);
        self::assertSame(
            $expectedLogLevel,
            (new ReflectionProperty(ProcessWithLockMiddleware::class, 'lockNotAcquiredLogLevel'))->getValue($middleware)
        );
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('provideSanityData')]
    public function testSanity(?array $configs = null): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        self::assertInstanceOf(Locker::class, $container->get(LockerInterface::class));
    }
}
