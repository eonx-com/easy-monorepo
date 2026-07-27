<?php
declare(strict_types=1);

namespace EonX\EasyTest\EasyErrorHandler\Trait;

use EonX\EasyTest\EasyErrorHandler\ErrorHandler\TraceableErrorHandlerStub;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \EonX\EasyTest\Common\Trait\ContainerServiceTrait
 */
trait EasyErrorHandlerAssertionTrait
{
    /**
     * @var int[]|null
     */
    private static ?array $errorHandlerReportedErrors = null;

    #[Before]
    public function setUpEasyErrorHandler(): void
    {
        self::$errorHandlerReportedErrors = null;
        TraceableErrorHandlerStub::reset();
    }

    #[After]
    public function tearDownEasyErrorHandler(): void
    {
        self::checkErrorHandlerReportedErrorsAsserted();
    }

    protected static function assertErrorHandlerReportedError(?int $errorCode = null): void
    {
        if ($errorCode === null) {
            return;
        }

        if ($errorCode === 0) {
            self::fail('Error code 0 is not allowed to be asserted.');
        }

        foreach (self::getErrorHandlerReportedErrors() as $key => $reportedError) {
            if ($reportedError === $errorCode) {
                unset(self::$errorHandlerReportedErrors[$key]);

                return;
            }
        }

        self::fail(
            \sprintf('There is no "%s" error code in reported errors.', $errorCode)
            . (\count(self::getErrorHandlerReportedErrors()) > 0
                ? \sprintf(' Not reported errors: %s.', \implode(', ', self::getErrorHandlerReportedErrors()))
                : ' No reported errors.')
        );
    }

    private static function checkErrorHandlerReportedErrorsAsserted(): void
    {
        self::assertEmpty(self::getErrorHandlerReportedErrors(), \sprintf(
            'Not all reported errors were asserted: %s.',
            \implode(', ', self::getErrorHandlerReportedErrors())
        ));
    }

    /**
     * @return array<int|class-string<\Throwable>>
     */
    private static function getErrorHandlerReportedErrors(): array
    {
        if (self::$errorHandlerReportedErrors !== null) {
            return self::$errorHandlerReportedErrors;
        }

        self::$errorHandlerReportedErrors = [];

        foreach (TraceableErrorHandlerStub::getAllReportedErrors() as $exception) {
            if (\is_int($exception->getCode()) && $exception->getCode() !== 0) {
                self::$errorHandlerReportedErrors[] = $exception->getCode();
            }
        }

        return self::$errorHandlerReportedErrors;
    }
}
