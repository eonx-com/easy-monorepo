<?php

declare(strict_types=1);

namespace EonX\EasyTest\Traits;

use Closure;
use RuntimeException;
use Throwable;

trait ExceptionTrait
{
    protected static bool $isInsideSafeCall = false;

    protected ?Throwable $thrownException = null;

    private bool $isNeedToAssertThrownException = false;

    /**
     * @param class-string<\Throwable>|null $previousException
     */
    protected function assertThrownException(
        string $expectedException,
        ?int $code = null,
        ?string $previousException = null
    ): void {
        $this->isNeedToAssertThrownException = false;

        self::assertNotNull($this->thrownException);

        if ($this->thrownException instanceof $expectedException === false) {
            throw $this->thrownException;
        }

        if (\str_starts_with($expectedException, 'App\\')) {
            $message = "Didn't you forget to define the error code?";
            self::assertNotNull($code, $message);
            self::assertNotSame(0, $code, $message);
        }

        if ($code !== null) {
            self::assertSame($code, $this->thrownException->getCode());
        }

        if ($previousException === null) {
            self::assertNull($this->thrownException->getPrevious());
        }

        if ($previousException !== null) {
            self::assertInstanceOf($previousException, $this->thrownException->getPrevious());
        }
    }

    protected function safeCall(Closure $func): void
    {
        self::$isInsideSafeCall = true;

        try {
            $this->isNeedToAssertThrownException = true;
            $func();
        } catch (Throwable $exception) {
            $this->thrownException = $exception;
        }

        self::$isInsideSafeCall = false;
    }

    private function checkThrownExceptionAsserted(): void
    {
        if ($this->isNeedToAssertThrownException) {
            throw new RuntimeException(
                'ExceptionTrait::safeCall() must be followed by ExceptionTrait::assertThrownException()'
            );
        }
    }
}
