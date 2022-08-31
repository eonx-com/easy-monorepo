<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ErrorLogLevelResolverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestGetErrorLogLevel(): iterable
    {
        yield 'Error because default' => [
            new \InvalidArgumentException(),
            Logger::ERROR,
        ];

        yield 'Debug default Symfony HTTP exception' => [
            new NotFoundHttpException(),
            Logger::DEBUG,
        ];

        yield 'Info from log levels mapping' => [
            new \InvalidArgumentException(),
            Logger::INFO,
            [
                \InvalidArgumentException::class => Logger::INFO,
            ],
        ];

        yield 'Critical from exception log level aware' => [
            (new BaseExceptionStub())->setCriticalLogLevel(),
            Logger::CRITICAL,
        ];
    }

    /**
     * @param int[] $exceptionLogLevels
     *
     * @dataProvider providerTestGetErrorLogLevel
     */
    public function testGetErrorLogLevel(
        \Throwable $throwable,
        int $expectedLogLevel,
        array $exceptionLogLevels
    ): void {
        $resolver = new ErrorLogLevelResolver($exceptionLogLevels);

        self::assertEquals($expectedLogLevel, $resolver->getLogLevel($throwable));
    }
}
