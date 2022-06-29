<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Bugsnag;

use EonX\EasyErrorHandler\Bridge\Bugsnag\BugsnagReporter;
use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Monolog\Logger;
use Throwable;

final class BugsnagReporterTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testReportWithIgnoredExceptionsResolver
     */
    public function provideDataForReportWithIgnoredExceptionsResolver(): iterable
    {
        yield 'Reported' => [
            false,
            (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(1),
        ];

        yield 'Ignored' => [
            true,
            (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(2),
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testReport
     */
    public function providerTestReport(): iterable
    {
        yield 'Report unexpected exception with no log level' => [true, new \Exception()];

        yield 'Report same log level as threshold' => [
            true,
            (new BaseExceptionStub())->setLogLevel(Logger::ERROR),
        ];

        yield 'Report higher log level as threshold' => [
            true,
            (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL),
        ];

        yield 'Do not report lower log level than threshold' => [
            false,
            (new BaseExceptionStub())->setLogLevel(Logger::INFO),
        ];

        yield 'Do not report ignored exceptions' => [
            false,
            (new BaseExceptionStub())->setLogLevel(Logger::ERROR),
            null,
            [BaseExceptionStub::class],
        ];
    }

    /**
     * @dataProvider providerTestReport
     *
     * @param null|string[] $ignoredExceptions
     */
    public function testReport(
        bool $shouldReport,
        Throwable $throwable,
        ?int $threshold = null,
        ?array $ignoredExceptions = null
    ): void {
        $stub = new BugsnagClientStub();
        $logLevelResolver = new ErrorLogLevelResolver();
        $reporter = new BugsnagReporter($stub, $logLevelResolver, $threshold, $ignoredExceptions);

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldReport, \count($stub->getCalls()) > 0);
    }

    /**
     * @dataProvider provideDataForReportWithIgnoredExceptionsResolver
     */
    public function testReportWithIgnoredExceptionsResolver(bool $shouldIgnore, Throwable $throwable): void
    {
        $exceptionsResolver = new class() implements BugsnagIgnoreExceptionsResolverInterface {
            public static function shouldIgnore(Throwable $throwable): bool
            {
                /** @var \EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub $throwable */
                return $throwable->getSubCode() === 2;
            }
        };
        $stub = new BugsnagClientStub();
        $logLevelResolver = new ErrorLogLevelResolver();
        $reporter = new BugsnagReporter(
            $stub,
            $logLevelResolver,
            null,
            [],
            \get_class($exceptionsResolver)
        );

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldIgnore, \count($stub->getCalls()) === 0);
    }
}
