<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Resolvers;

use EonX\EasyErrorHandler\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use Throwable;

final class BugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool
    {
        return false;
    }
}
