<?php

declare(strict_types=1);

namespace EonX\EasyLock\Exceptions;

use EonX\EasyLock\Interfaces\EasyLockExceptionInterface;

final class ShouldRetryException extends \RuntimeException implements EasyLockExceptionInterface
{
    // No body needed.
}
