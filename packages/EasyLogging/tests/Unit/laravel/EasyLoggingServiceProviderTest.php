<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Unit\Laravel;

use EonX\EasyLogging\Bundle\Enum\BundleParam;
use EonX\EasyLogging\Factory\LoggerFactoryInterface;
use EonX\EasyLogging\Logger\LazyLogger;
use Illuminate\Container\EntryNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;

final class EasyLoggingServiceProviderTest extends AbstractLaravelTestCase
{
    /**
     * @see testChannelParameterOnMake
     */
    public static function provideChannelParameterOnMakeData(): iterable
    {
        yield 'Default' => [null];
        yield 'App' => ['app'];
        yield 'Custom' => ['custom'];
    }

    #[DataProvider('provideChannelParameterOnMakeData')]
    public function testChannelParameterOnMake(?string $channel): void
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->getApp()
            ->make(LoggerInterface::class, [BundleParam::KeyChannel->value => $channel]);

        self::assertSame($channel ?? 'app', $logger->getName());
    }

    public function testDefaultLoggerNotOverriddenBecauseOfConfig(): void
    {
        $this->expectException(EntryNotFoundException::class);

        $app = $this->getApp([
            'easy-logging.override_default_logger' => false,
        ]);

        $app->get(LoggerInterface::class);
    }

    public function testLazyLogger(): void
    {
        $app = $this->getApp([
            'easy-logging.lazy_loggers' => ['lazy'],
        ]);

        $loggerFactory = $app->get(LoggerFactoryInterface::class);

        self::assertInstanceOf(LazyLogger::class, $loggerFactory->create('lazy'));
        self::assertNotInstanceOf(LazyLogger::class, $loggerFactory->create('any'));
    }

    public function testLazyLoggersWildcard(): void
    {
        $app = $this->getApp([
            'easy-logging.lazy_loggers' => ['*'],
        ]);

        $loggerFactory = $app->get(LoggerFactoryInterface::class);

        self::assertInstanceOf(LazyLogger::class, $loggerFactory->create('any'));
    }

    public function testSanity(): void
    {
        $app = $this->getApp();

        self::assertInstanceOf(LoggerFactoryInterface::class, $app->get(LoggerFactoryInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get(LoggerInterface::class));
        self::assertInstanceOf(LoggerInterface::class, $app->get('logger'));
    }
}
