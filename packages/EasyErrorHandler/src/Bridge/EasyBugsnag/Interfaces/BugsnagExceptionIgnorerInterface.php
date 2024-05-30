<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces;

use Throwable;

interface BugsnagExceptionIgnorerInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
