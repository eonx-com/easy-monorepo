<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Resolver;

use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
use EonX\EasyErrorHandler\Tests\Stub\Exception\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use InvalidArgumentException;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ErrorLogLevelResolverTest extends AbstractUnitTestCase
{
    /**
     * @see testGetErrorLogLevel
     */
    public static function provideGetErrorLogLevelData(): iterable
    {
        yield 'Error because default' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Level::Error,
            'environment' => 'test',
        ];

        yield 'Debug default Symfony HTTP exception' => [
            'throwable' => new NotFoundHttpException(),
            'expectedLogLevel' => Level::Debug,
            'environment' => 'test',
        ];

        yield 'Debug default Symfony request exception' => [
            'throwable' => new SuspiciousOperationException(),
            'expectedLogLevel' => Level::Debug,
            'environment' => 'test',
        ];

        yield 'Info from log levels mapping' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Level::Info,
            'environment' => 'set_ignored_exceptions',
        ];

        yield 'Critical from exception log level aware' => [
            'throwable' => new BaseExceptionStub()
                ->setCriticalLogLevel(),
            'expectedLogLevel' => Level::Critical,
            'environment' => 'test',
        ];
    }

    #[DataProvider('provideGetErrorLogLevelData')]
    public function testGetErrorLogLevel(Throwable $throwable, Level $expectedLogLevel, string $environment): void
    {
        self::bootKernel(['environment' => $environment]);
        $sut = self::getService(ErrorLogLevelResolverInterface::class);

        $logLevel = $sut->getLogLevel($throwable);

        self::assertSame($expectedLogLevel, $logLevel);
    }
}
