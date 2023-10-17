<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Exceptions\RetryableException;
use EonX\EasyErrorHandler\Resolvers\DefaultIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Tests\Stubs\LoggerStub;
use EonX\EasyErrorHandler\Tests\Stubs\TranslatorStub;
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
    public static function provideExceptions(): iterable
    {
        $message = 'message';
        yield 'Simple exception' => [
            'throwable' => new Exception($message),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(1, $logger->getRecords());
                $record = $logger->getRecords()[0];
                self::assertNotEmpty($record['level']);
                self::assertNotEmpty($record['message']);
                self::assertNotEmpty($record['context']['exception']);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [new Exception($message)]),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(1, $logger->getRecords());
                $record = $logger->getRecords()[0];
                self::assertNotEmpty($record['level']);
                self::assertNotEmpty($record['message']);
                self::assertNotEmpty($record['context']['exception']);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException More Than 1' => [
            'throwable' => new HandlerFailedException(Envelope::wrap(new stdClass()), [
                new Exception($message),
                new Exception($message),
            ]),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(2, $logger->getRecords());
                $recordA = $logger->getRecords()[0];
                self::assertNotEmpty($recordA['level']);
                self::assertNotEmpty($recordA['message']);
                self::assertNotEmpty($recordA['context']['exception']);
                $recordB = $logger->getRecords()[1];
                self::assertNotEmpty($recordB['level']);
                self::assertNotEmpty($recordB['message']);
                self::assertNotEmpty($recordB['context']['exception']);
            },
        ];

        yield 'Retryable Exception attempt not reported' => [
            'throwable' => RetryableException::fromThrowable(new Exception($message), true),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(0, $logger->getRecords());
            },
        ];

        yield 'Retryable Exception reported as it will not retry' => [
            'throwable' => RetryableException::fromThrowable(new Exception($message), false),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(1, $logger->getRecords());
                $record = $logger->getRecords()[0];
                self::assertNotEmpty($record['level']);
                self::assertNotEmpty($record['message']);
                self::assertNotEmpty($record['context']['exception']);
            },
        ];

        yield 'Retryable Exception attempt reported as configured' => [
            'throwable' => RetryableException::fromThrowable(new Exception($message), true),
            'assertions' => static function (LoggerStub $logger): void {
                self::assertCount(1, $logger->getRecords());
                $record = $logger->getRecords()[0];
                self::assertNotEmpty($record['level']);
                self::assertNotEmpty($record['message']);
                self::assertNotEmpty($record['context']['exception']);
            },
            'reportRetryableExceptionAttempts' => true,
        ];
    }

    #[DataProvider('provideExceptions')]
    public function testReport(
        Throwable $throwable,
        callable $assertions,
        ?bool $reportRetryableExceptionAttempts = null,
    ): void {
        $logger = new LoggerStub();
        $translator = new TranslatorStub();
        $verboseStrategy = new ChainVerboseStrategy([], false);
        $errorDetailsResolver = new ErrorDetailsResolver($logger, $translator);
        $errorLogLevelResolver = new ErrorLogLevelResolver();
        $ignoreExceptionsResolver = new DefaultIgnoreExceptionsResolver(
            reportRetryableExceptionAttempts: $reportRetryableExceptionAttempts
        );
        $errorHandler = new ErrorHandler(
            new ErrorResponseFactory(),
            $logger,
            $verboseStrategy,
            $errorDetailsResolver,
            $errorLogLevelResolver,
            $ignoreExceptionsResolver,
            [],
        );

        $errorHandler->report($throwable);

        $assertions($logger);
    }
}
