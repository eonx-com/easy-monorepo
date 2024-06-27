<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Resolver;

use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\Stub\Exception\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use InvalidArgumentException;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ErrorLogLevelResolverTest extends AbstractUnitTestCase
{
    /**
     * @see testGetErrorLogLevel
     */
    public static function providerTestGetErrorLogLevel(): iterable
    {
        yield 'Error because default' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Logger::ERROR,
            'exceptionLogLevels' => null,
        ];

        yield 'Debug default Symfony HTTP exception' => [
            'throwable' => new NotFoundHttpException(),
            'expectedLogLevel' => Logger::DEBUG,
            'exceptionLogLevels' => null,
        ];

        yield 'Debug default Symfony request exception' => [
            'throwable' => new SuspiciousOperationException(),
            'expectedLogLevel' => Logger::DEBUG,
            'exceptionLogLevels' => null,
        ];

        yield 'Info from log levels mapping' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Logger::INFO,
            'exceptionLogLevels' => [
                InvalidArgumentException::class => Logger::INFO,
            ],
        ];

        yield 'Critical from exception log level aware' => [
            'throwable' => (new BaseExceptionStub())->setCriticalLogLevel(),
            'expectedLogLevel' => Logger::CRITICAL,
            'exceptionLogLevels' => null,
        ];
    }

    /**
     * @param array<class-string, int> $exceptionLogLevels
     */
    #[DataProvider('providerTestGetErrorLogLevel')]
    public function testGetErrorLogLevel(
        Throwable $throwable,
        int $expectedLogLevel,
        ?array $exceptionLogLevels = null,
    ): void {
        $resolver = new ErrorLogLevelResolver($exceptionLogLevels);

        $logLevel = $resolver->getLogLevel($throwable);

        self::assertSame($expectedLogLevel, $logLevel);
    }
}
