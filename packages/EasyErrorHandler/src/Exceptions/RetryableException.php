<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Exceptions;

use Exception;
use Throwable;

final class RetryableException extends Exception
{
    public function __construct(
        Throwable $previous,
        private readonly bool $willRetry
    ) {
        parent::__construct(previous: $previous);
    }

    public static function fromThrowable(Throwable $throwable, bool $willRetry): self
    {
        if ($throwable instanceof self) {
            return $throwable;
        }

        return new self($throwable, $willRetry);
    }

    public function willRetry(): bool
    {
        return $this->willRetry;
    }
}
