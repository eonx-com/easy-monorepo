<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use InvalidArgumentException;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ErrorLogLevelResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestGetErrorLogLevel(): iterable
    {
        yield 'Error because default' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Logger::ERROR,
        ];

        yield 'Debug default Symfony HTTP exception' => [
            'throwable' => new NotFoundHttpException(),
            'expectedLogLevel' => Logger::DEBUG,
        ];

        yield 'Critical from exception log level aware' => [
            'throwable' => (new BaseExceptionStub())->setCriticalLogLevel(),
            'expectedLogLevel' => Logger::CRITICAL,
        ];
    }

    /**
     * @dataProvider providerTestGetErrorLogLevel
     */
    public function testGetErrorLogLevel(
        Throwable $throwable,
        int $expectedLogLevel
    ): void {
        $resolver = new ErrorLogLevelResolver();

        $logLevel = $resolver->getLogLevel($throwable);

        self::assertSame($expectedLogLevel, $logLevel);
    }

    public function testGetErrorLogLevelToGetInfoFromLogLevelsMapping(): void
    {
        $resolver = new ErrorLogLevelResolver([InvalidArgumentException::class => Logger::INFO]);

        $logLevel = $resolver->getLogLevel(new InvalidArgumentException());

        self::assertSame(Logger::INFO, $logLevel);
    }
}
