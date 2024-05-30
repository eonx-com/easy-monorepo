<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\EasyBugsnag\Reporters;

use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Ignorers\DefaultBugsnagExceptionIgnorer;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Reporters\BugsnagErrorReporter;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;

final class BugsnagReporterTest extends AbstractTestCase
{
    /**
     * @see testReportWithIgnoredExceptionsResolver
     */
    public static function provideDataForReportWithIgnoredExceptionsResolver(): iterable
    {
        yield 'Reported' => [
            'shouldIgnore' => false,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(1),
        ];

        yield 'Ignored' => [
            'shouldIgnore' => true,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(2),
        ];
    }

    /**
     * @see testReport
     */
    public static function providerTestReport(): iterable
    {
        yield 'Report unexpected exception with no log level' => [
            'shouldReport' => true,
            'throwable' => new Exception(),
            'threshold' => null,
            'ignoredExceptions' => null,
        ];

        yield 'Report same log level as threshold' => [
            'shouldReport' => true,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::ERROR),
            'threshold' => Logger::ERROR,
            'ignoredExceptions' => null,
        ];

        yield 'Report higher log level as threshold' => [
            'shouldReport' => true,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL),
            'threshold' => Logger::ERROR,
            'ignoredExceptions' => null,
        ];

        yield 'Do not report lower log level than threshold' => [
            'shouldReport' => false,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL),
            'threshold' => Logger::EMERGENCY,
            'ignoredExceptions' => null,
        ];

        yield 'Do not report ignored exceptions' => [
            'shouldReport' => false,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::ERROR),
            'threshold' => Logger::ERROR,
            'ignoredExceptions' => [BaseExceptionStub::class],
        ];
    }

    /**
     * @param class-string[]|null $ignoredExceptions
     */
    #[DataProvider('providerTestReport')]
    public function testReport(
        bool $shouldReport,
        Throwable $throwable,
        ?int $threshold = null,
        ?array $ignoredExceptions = null,
    ): void {
        $stub = new BugsnagClientStub();
        $reporter = new BugsnagErrorReporter(
            $stub,
            [new DefaultBugsnagExceptionIgnorer($ignoredExceptions ?? [])],
            new ErrorLogLevelResolver(),
            $threshold
        );

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldReport, \count($stub->getCalls()) > 0);
    }

    #[DataProvider('provideDataForReportWithIgnoredExceptionsResolver')]
    public function testReportWithIgnoredExceptionsResolver(bool $shouldIgnore, Throwable $throwable): void
    {
        $exceptionIgnorer = new class() implements BugsnagExceptionIgnorerInterface {
            public function shouldIgnore(Throwable $throwable): bool
            {
                if ($throwable instanceof BaseExceptionStub) {
                    return $throwable->getSubCode() === 2;
                }

                return false;
            }
        };
        $stub = new BugsnagClientStub();
        $reporter = new BugsnagErrorReporter(
            $stub,
            [$exceptionIgnorer],
            new ErrorLogLevelResolver(),
            null
        );

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldIgnore, \count($stub->getCalls()) === 0);
    }
}
