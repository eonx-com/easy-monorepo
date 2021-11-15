<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests;

use EonX\EasyErrorHandler\ErrorHandler;
use EonX\EasyErrorHandler\Reporters\FromIterableReporterProvider;
use EonX\EasyErrorHandler\Response\ErrorResponseFactory;
use EonX\EasyErrorHandler\Tests\Stubs\ErrorReporterStub;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class ErrorHandlerTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestReport(): iterable
    {
        yield 'Simple report' => [
            new \Exception('message'),
            static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(\Exception::class, $reporter->getReportedErrors()[0]);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException' => [
            new HandlerFailedException(Envelope::wrap(new \stdClass()), [new \Exception('message')]),
            static function (ErrorReporterStub $reporter): void {
                self::assertCount(1, $reporter->getReportedErrors());
                self::assertInstanceOf(\Exception::class, $reporter->getReportedErrors()[0]);
            },
        ];

        yield 'Symfony Messenger HandlerFailedException More Than 1' => [
            new HandlerFailedException(Envelope::wrap(new \stdClass()), [
                new \Exception('message'),
                new \Exception('message'),
            ]),
            static function (ErrorReporterStub $reporter): void {
                self::assertCount(2, $reporter->getReportedErrors());
                self::assertInstanceOf(\Exception::class, $reporter->getReportedErrors()[0]);
                self::assertInstanceOf(\Exception::class, $reporter->getReportedErrors()[1]);
            },
        ];
    }

    /**
     * @param null|string[] $ignoredExceptions
     *
     * @dataProvider providerTestReport
     */
    public function testReport(\Throwable $throwable, callable $test, ?array $ignoredExceptions = null): void
    {
        $reporter = new ErrorReporterStub();
        $reporterProviders = [new FromIterableReporterProvider([$reporter])];
        $errorHandler = new ErrorHandler(new ErrorResponseFactory(), [], $reporterProviders, false, $ignoredExceptions);

        $errorHandler->report($throwable);

        $test($reporter);
    }
}
