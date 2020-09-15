<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Bugsnag;

use EonX\EasyErrorHandler\Bridge\Bugsnag\BugsnagReporter;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use EonX\EasyErrorHandler\Tests\Stubs\BaseExceptionStub;
use EonX\EasyErrorHandler\Tests\Stubs\BugsnagClientStub;
use Monolog\Logger;
use Throwable;

final class BugsnagReporterTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestReport(): iterable
    {
        yield 'Report unexpected exception with no log level' => [
            true,
            new \Exception(),
        ];

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
    }

    /**
     * @dataProvider providerTestReport
     */
    public function testReport(bool $shouldReport, Throwable $throwable, ?int $threshold = null): void
    {
        $stub = new BugsnagClientStub();
        $reporter = new BugsnagReporter($stub, $threshold);

        $reporter->report($throwable);

        self::assertEquals(0, $reporter->getPriority());
        self::assertEquals($shouldReport, \count($stub->getCalls()) > 0);
    }
}
