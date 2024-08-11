<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\Resolver;

use EonX\EasyErrorHandler\Common\Resolver\ErrorLogLevelResolverInterface;
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
    public static function provideGetErrorLogLevelData(): iterable
    {
        yield 'Error because default' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Logger::ERROR,
            'environment' => 'test',
        ];

        yield 'Debug default Symfony HTTP exception' => [
            'throwable' => new NotFoundHttpException(),
            'expectedLogLevel' => Logger::DEBUG,
            'environment' => 'test',
        ];

        yield 'Debug default Symfony request exception' => [
            'throwable' => new SuspiciousOperationException(),
            'expectedLogLevel' => Logger::DEBUG,
            'environment' => 'test',
        ];

        yield 'Info from log levels mapping' => [
            'throwable' => new InvalidArgumentException(),
            'expectedLogLevel' => Logger::INFO,
            'environment' => 'set_ignored_exceptions',
        ];

        yield 'Critical from exception log level aware' => [
            'throwable' => (new BaseExceptionStub())->setCriticalLogLevel(),
            'expectedLogLevel' => Logger::CRITICAL,
            'environment' => 'test',
        ];
    }

    #[DataProvider('provideGetErrorLogLevelData')]
    public function testGetErrorLogLevel(Throwable $throwable, int $expectedLogLevel, string $environment): void
    {
        self::bootKernel(['environment' => $environment]);
        $sut = self::getService(ErrorLogLevelResolverInterface::class);

        $logLevel = $sut->getLogLevel($throwable);

        self::assertSame($expectedLogLevel, $logLevel);
    }
}
