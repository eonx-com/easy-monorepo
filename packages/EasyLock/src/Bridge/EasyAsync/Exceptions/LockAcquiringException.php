<?php

declare(strict_types=1);

namespace EonX\EasyLock\Bridge\EasyAsync\Exceptions;

use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface;
use EonX\EasyLock\Interfaces\EasyLockExceptionInterface;

final class LockAcquiringException extends \RuntimeException
    implements EasyLockExceptionInterface, ShouldKillWorkerExceptionInterface
{
    // No body needed.
}
