<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Resolvers;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\BugsnagIgnoreExceptionsResolverInterface;
use EonX\EasyErrorHandler\Builders\ValidationErrorResponseBuilder;
use Throwable;

final class BugsnagIgnoredExceptionsResolver implements BugsnagIgnoreExceptionsResolverInterface
{
    public static function shouldIgnore(Throwable $throwable): bool
    {
        return ValidationErrorResponseBuilder::supports($throwable);
    }
}
