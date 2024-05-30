<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Ignorers;

use EonX\EasyErrorHandler\Bridge\EasyBugsnag\Interfaces\BugsnagExceptionIgnorerInterface;
use Throwable;

final class DefaultBugsnagExceptionIgnorer implements BugsnagExceptionIgnorerInterface
{
    /**
     * @template TThrowable of \Throwable
     *
     * @param class-string<TThrowable>[] $ignoredExceptions
     */
    public function __construct(private readonly array $ignoredExceptions)
    {
    }

    public function shouldIgnore(Throwable $throwable): bool
    {
        foreach ($this->ignoredExceptions as $ignoreClass) {
            if (\is_a($throwable, $ignoreClass)) {
                return true;
            }
        }

        return false;
    }
}
