<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bridge\EasyAsync\Exceptions;

use EonX\EasyAsync\Interfaces\ShouldKillWorkerExceptionInterface as ShouldKillWorker;
use EonX\EasyLock\Interfaces\EasyLockExceptionInterface;
use RuntimeException;

final class LockAcquiringException extends RuntimeException implements EasyLockExceptionInterface, ShouldKillWorker
{
    // No body needed
}
