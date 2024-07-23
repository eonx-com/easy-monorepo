<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Unit\Common\ErrorHandler;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Common\Exception\RetryableException;
use EonX\EasyErrorHandler\Common\Factory\ErrorResponseFactory;
use EonX\EasyErrorHandler\Common\Provider\FromIterableErrorReporterProvider;
use EonX\EasyErrorHandler\Common\Strategy\ChainVerboseStrategy;
use EonX\EasyErrorHandler\Tests\Stub\Reporter\ErrorReporterStub;
use EonX\EasyErrorHandler\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyWebhook\Messenger\Exception\UnrecoverableWebhookMessageException;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Throwable;

final class ErrorHandlerTest extends AbstractUnitTestCase
{
    /**
     * @see testRepeatedExceptionReport
     */
    public static function provideRepeatedExceptionReportData(): iterable
    {
        yield 'Skip reported exceptions' => [true, 1];
        yield 'Report all exceptions' => [false, 2];
    }

    /**
     * @see testReport
     */
    public static function provideReportData(): iterable
    {
        yield 'Simple report' => [
            'throwable' => new Exception('message'),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [new Exception('message')]),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException More Than 1' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [
                new Exception('message'),
                new Exception('message'),
            ]),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(2, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
                self::assertSame(Exception::class, $reporter->getReportedErrors()[1]::class);
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
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
        ];

        yield 'Retryable Exception attempt reported as configured' => [
            'throwable' => RetryableException::fromThrowable(new Exception(), true),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
            'reportRetryableExceptionAttempts' => true,
        ];

        yield 'Symfony Messenger UnrecoverableMessageHandlingException' => [
            'throwable' => new UnrecoverableMessageHandlingException(previous: new Exception()),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
        ];

        yield 'EasyWebhook UnrecoverableWebhookMessageException' => [
            'throwable' => new UnrecoverableWebhookMessageException(previous: new Exception()),
            'assertions' => static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertSame(Exception::class, $reporter->getReportedErrors()[0]::class);
            },
        ];
    }

    #[DataProvider('provideRepeatedExceptionReportData')]
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

    #[DataProvider('provideReportData')]
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
}
