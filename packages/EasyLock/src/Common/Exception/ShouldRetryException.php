<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\Exception;

use RuntimeException;

final class ShouldRetryException extends RuntimeException implements EasyLockExceptionInterface
{
    // No body needed
}
