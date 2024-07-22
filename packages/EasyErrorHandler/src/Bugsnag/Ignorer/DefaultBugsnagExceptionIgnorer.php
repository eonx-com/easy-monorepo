<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Ignorer;

use Throwable;

final readonly class DefaultBugsnagExceptionIgnorer implements BugsnagExceptionIgnorerInterface
{
    /**
     * @template TThrowable of \Throwable
     *
     * @param class-string<TThrowable>[] $ignoredExceptions
     */
    public function __construct(
        private array $ignoredExceptions,
    ) {
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
