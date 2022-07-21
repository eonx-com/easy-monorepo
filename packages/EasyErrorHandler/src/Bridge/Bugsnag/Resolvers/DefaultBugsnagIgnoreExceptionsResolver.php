<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag\Resolvers;

use EonX\EasyErrorHandler\Bridge\Bugsnag\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use Throwable;

final class DefaultBugsnagIgnoreExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool
    {
        return false;
    }
}
