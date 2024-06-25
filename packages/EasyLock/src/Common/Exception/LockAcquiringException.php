<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\Exception;

use EonX\EasyAsync\Common\Exception\ShouldKillWorkerExceptionInterface;
use RuntimeException;

final class LockAcquiringException extends RuntimeException implements
    EasyLockExceptionInterface, ShouldKillWorkerExceptionInterface
{
    // No body needed
}
