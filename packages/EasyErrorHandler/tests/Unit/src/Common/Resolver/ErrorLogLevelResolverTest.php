<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Resolver;

use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolver;
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
            'exceptionLogLevels' => null,
        ];

        yield 'Debug default Symfony HTTP exception' => [
            'throwable' => new NotFoundHttpException(),
            'expectedLogLevel' => Level::Debug,
            'exceptionLogLevels' => null,
        ];

        yield 'Debug default Symfony request exception' => [
            'throwable' => new SuspiciousOperationException(),
            'expectedLogLevel' => Level::Debug,
            'exceptionLogLevels' => null,
        ];

        yield 'Info from log levels mapping' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Level::Info,
            'exceptionLogLevels' => [
                InvalidArgumentException::class => Level::Info,
            ],
        ];

        yield 'Critical from exception log level aware' => [
            'throwable' => (new BaseExceptionStub())->setCriticalLogLevel(),
            'expectedLogLevel' => Level::Critical,
            'exceptionLogLevels' => null,
        ];
    }

    /**
     * @param array<class-string, int> $exceptionLogLevels
     */
    #[DataProvider('provideGetErrorLogLevelData')]
    public function testGetErrorLogLevel(
        Throwable $throwable,
        Level $expectedLogLevel,
        ?array $exceptionLogLevels = null,
    ): void {
        $resolver = new ErrorLogLevelResolver($exceptionLogLevels);

        $logLevel = $resolver->getLogLevel($throwable);

        self::assertSame($expectedLogLevel, $logLevel);
    }
}
