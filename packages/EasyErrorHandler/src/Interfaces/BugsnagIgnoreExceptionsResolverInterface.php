<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Throwable;

interface BugsnagIgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
