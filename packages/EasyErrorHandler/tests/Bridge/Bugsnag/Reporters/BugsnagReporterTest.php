<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Bugsnag\Reporters;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Reporters\BugsnagErrorReporter;
use EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers\DefaultBugsnagIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Exception;
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
            'shouldIgnore' => false,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(1),
        ];

        yield 'Ignored' => [
            'shouldIgnore' => true,
            'throwable' => (new BaseExceptionStub())->setLogLevel(Logger::CRITICAL)->setSubCode(2),
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testReport
     */
    public function providerTestReport(): iterable
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
     * @dataProvider providerTestReport
     *
     * @param null|class-string[] $ignoredExceptions
     */
    public function testReport(
        bool $shouldReport,
        Throwable $throwable,
        ?int $threshold = null,
        ?array $ignoredExceptions = null
    ): void {
        $stub = new BugsnagClientStub();
        $reporter = new BugsnagErrorReporter(
            $stub,
            new DefaultBugsnagIgnoreExceptionsResolver($ignoredExceptions),
            new ErrorLogLevelResolver(),
            $threshold
        );

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldReport, \count($stub->getCalls()) > 0);
    }

    /**
     * @dataProvider provideDataForReportWithIgnoredExceptionsResolver
     */
    public function testReportWithIgnoredExceptionsResolver(bool $shouldIgnore, Throwable $throwable): void
    {
        $ignoreExceptionsResolver = new class() implements BugsnagIgnoreExceptionsResolverInterface {
            public function shouldIgnore(Throwable $throwable): bool
            {
                /** @var \EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub $throwable */
                return $throwable->getSubCode() === 2;
            }
        };
        $stub = new BugsnagClientStub();
        $reporter = new BugsnagErrorReporter(
            $stub,
            $ignoreExceptionsResolver,
            new ErrorLogLevelResolver(),
            null
        );

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldIgnore, \count($stub->getCalls()) === 0);
    }
}
