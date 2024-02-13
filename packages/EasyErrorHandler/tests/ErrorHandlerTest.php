<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Exceptions\RetryableException;
use EonX\EasyErrorHandler\Providers\FromIterableErrorReporterProvider;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Tests\Stubs\ErrorReporterStub;
use EonX\EasyErrorHandler\Verbose\ChainVerboseStrategy;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

final class ErrorHandlerTest extends AbstractTestCase
{
    /**
     * @see testReport
     */
    public static function providerTestReport(): iterable
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

        yield 'Retryable Exception attempt not reported' => [
            'throwable' => RetryableException::fromThrowable(new Exception(), true),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(0, $reporter->getReportedErrors());
            },
        ];

        yield 'Retryable Exception reported as it will not retry' => [
            'throwable' => RetryableException::fromThrowable(new Exception(), false),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[0]);
            },
        ];

        yield 'Retryable Exception attempt reported as configured' => [
            'throwable' => RetryableException::fromThrowable(new Exception(), true),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(Exception::class, $reporter->getReportedErrors()[0]);
            },
            'reportRetryableExceptionAttempts' => true,
        ];
    }

    /**
     * @see testRepeatedExceptionReport
     */
    public static function providerTestRepeatedExceptionReport(): iterable
    {
        yield 'Skip reported exceptions' => [true, 1];
        yield 'Report all exceptions' => [false, 2];
    }

    #[DataProvider('providerTestReport')]
    public function testReport(
        Throwable $throwable,
        callable $assertions,
        ?bool $reportRetryableExceptionAttempts = null,
    ): void {
        $reporter = new ErrorReporterStub();
        $reporterProviders = [new FromIterableErrorReporterProvider([$reporter])];
        $verboseStrategy = new ChainVerboseStrategy([], false);
        $errorHandler = new ErrorHandler(
            new ErrorResponseFactory(),
            [],
            $reporterProviders,
            $verboseStrategy,
            reportRetryableExceptionAttempts: $reportRetryableExceptionAttempts
        );

        $errorHandler->report($throwable);

        $assertions($reporter);
    }

    #[DataProvider('providerTestRepeatedExceptionReport')]
    public function testRepeatedExceptionReport(bool $skipReportedExceptions, int $expectedReportedErrorsCount): void
    {
        $throwable = new Exception('message');
        $reporter = new ErrorReporterStub();
        $reporterProviders = [new FromIterableErrorReporterProvider([$reporter])];
        $verboseStrategy = new ChainVerboseStrategy([], false);
        $errorHandler = new ErrorHandler(
            new ErrorResponseFactory(),
            [],
            $reporterProviders,
            $verboseStrategy,
            skipReportedExceptions: $skipReportedExceptions,
        );

        $errorHandler->report($throwable);
        $errorHandler->report($throwable);

        self::assertCount($expectedReportedErrorsCount, $reporter->getReportedErrors());
    }
}
