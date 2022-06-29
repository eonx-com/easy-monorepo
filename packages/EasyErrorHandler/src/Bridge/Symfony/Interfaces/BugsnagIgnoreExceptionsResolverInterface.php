<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Interfaces;

use Throwable;

interface BugsnagIgnoreExceptionsResolverInterface
{
    public static function shouldIgnore(Throwable $throwable): bool;
}
