<?php
declare(strict_types=1);

namespace EonX\EasyTest\EasyErrorHandler\Trait;

use EonX\EasyErrorHandler\Common\ErrorHandler\ErrorHandlerInterface;

/**
 * @mixin \PHPUnit\Framework\TestCase
 * @mixin \EonX\EasyTest\Common\Trait\ContainerServiceTrait
 */
trait EasyErrorHandlerAssertionTrait
{
    protected function assertEmptyReportedErrors(): void
    {
        self::assertCount(
            0,
            $this->getErrorHandlerReportedThrowables(),
            'The list of reported errors is not empty.'
        );
    }

    protected function assertErrorCodeExistsInReportedErrors(int $errorCode): void
    {
        $errorCodeExists = false;
        foreach ($this->getErrorHandlerReportedThrowables() as $exception) {
            if ($exception->getCode() === $errorCode) {
                $errorCodeExists = true;

                break;
            }
        }

        self::assertTrue(
            $errorCodeExists,
            \sprintf('There is no error with the "%s" error code in reported errors.', $errorCode)
        );
    }

    /**
     * @return \Throwable[]
     */
    protected function getErrorHandlerReportedThrowables(): array
    {
        /** @var \EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface $errorHandler */
        $errorHandler = self::getService(ErrorHandlerInterface::class);

        return $errorHandler->getReportedErrors();
    }
}
