<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Ignorer;

use Throwable;

interface BugsnagExceptionIgnorerInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
