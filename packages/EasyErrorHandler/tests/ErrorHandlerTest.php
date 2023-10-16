<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Resolvers\DefaultIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorDetailsResolver;
use EonX\EasyErrorHandler\Resolvers\ErrorLogLevelResolver;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Tests\Stubs\LoggerStub;
use EonX\EasyErrorHandler\Tests\Stubs\TranslatorStub;
use EonX\EasyErrorHandler\Verbose\ChainVerboseStrategy;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
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
    }

    #[DataProvider('provideExceptions')]
    public function testReport(Throwable $throwable, callable $assertions): void
    {
        $logger = new LoggerStub();
        $translator = new TranslatorStub();
        $verboseStrategy = new ChainVerboseStrategy([], false);
        $errorDetailsResolver = new ErrorDetailsResolver($logger, $translator);
        $errorLogLevelResolver = new ErrorLogLevelResolver();
        $ignoreExceptionsResolver = new DefaultIgnoreExceptionsResolver();
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
