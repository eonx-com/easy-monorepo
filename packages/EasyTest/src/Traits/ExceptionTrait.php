<?php
declare(strict_types=1);

namespace EonX\EasyTest\Traits;

use Closure;
use PHPUnit\Util\Color;
use RuntimeException;
use Throwable;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait ExceptionTrait
{
    protected static bool $isInsideSafeCall = false;

    protected ?Throwable $thrownException = null;

    private bool $isThrownExceptionAssertionNeeded = false;

    /**
     * @param class-string<\Throwable> $expectedException
     * @param class-string<\Throwable>|null $previousException
     */
    protected function assertThrownException(
        string $expectedException,
        ?int $code = null,
        ?string $previousException = null,
    ): void {
        $this->isThrownExceptionAssertionNeeded = false;

        self::assertNotNull($this->thrownException);

        if ($this->thrownException instanceof $expectedException === false) {
            echo \PHP_EOL;
            echo Color::colorize(
                'fg-red',
                'Expected ' . $expectedException . ' but got ' . $this->thrownException::class
            );
            echo \PHP_EOL;

            throw $this->thrownException;
        }

        if (
            $code === null
            && $this->thrownException->getCode() !== 0
            && \str_starts_with($expectedException, 'App\\')
        ) {
            self::assertNotNull($code, "Didn't you forget to define the error code?");
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
            $this->isThrownExceptionAssertionNeeded = true;
            $func();
        } catch (Throwable $exception) {
            $this->thrownException = $exception;
        }

        self::$isInsideSafeCall = false;
    }

    /**
     * This check is needed to make sure that the developer is not forgetting to assert the thrown exception,
     * when using safeCall().
     *
     * This check should be called in the tearDown() method of the test class.
     */
    private function checkThrownExceptionAssertion(): void
    {
        if ($this->isThrownExceptionAssertionNeeded) {
            throw new RuntimeException(
                'ExceptionTrait::safeCall() must be followed by ExceptionTrait::assertThrownException()'
            );
        }
    }
}
