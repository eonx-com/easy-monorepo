<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces;

use Throwable;

interface BugsnagIgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
