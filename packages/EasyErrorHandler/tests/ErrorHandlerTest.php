<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Providers\FromIterableErrorReporterProvider;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Tests\Stubs\ErrorReporterStub;
use EonX\EasyErrorHandler\Verbose\ChainVerboseStrategy;
use Exception;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

final class ErrorHandlerTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestReport(): iterable
    {
        yield 'Simple report' => [
            'throwable' => new Exception('message'),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[0]);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [new Exception('message')]),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[0]);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException More Than 1' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [
                new Exception('message'),
                new Exception('message'),
            ]),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(2, $reporter->getReportedErrors());
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[0]);
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[1]);
            },
        ];
    }

    /**
     * @dataProvider providerTestReport
     */
    public function testReport(Throwable $throwable, callable $assertions): void
    {
        $reporter = new ErrorReporterStub();
        $reporterProviders = [new FromIterableErrorReporterProvider([$reporter])];
        $verboseStrategy = new ChainVerboseStrategy([], false);
        $errorHandler = new ErrorHandler(
            new ErrorResponseFactory(),
            [],
            $reporterProviders,
            $verboseStrategy
        );

        $errorHandler->report($throwable);

        $assertions($reporter);
    }
}
