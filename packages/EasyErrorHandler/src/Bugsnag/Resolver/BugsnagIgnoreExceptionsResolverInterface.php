<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Resolver;

use Throwable;

interface BugsnagIgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
