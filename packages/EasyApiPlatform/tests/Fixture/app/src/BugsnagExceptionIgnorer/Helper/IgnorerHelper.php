<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\BugsnagExceptionIgnorer\Helper;

use Throwable;

final readonly class IgnorerHelper
{
    /**
     * @param \EonX\EasyErrorHandler\Bugsnag\Ignorer\BugsnagExceptionIgnorerInterface[] $exceptionIgnorers
     */
    public function __construct(
        private iterable $exceptionIgnorers,
    ) {
    }

    public function isIgnored(Throwable $throwable): bool
    {
        foreach ($this->exceptionIgnorers as $ignorer) {
            if ($ignorer->shouldIgnore($throwable)) {
                return true;
            }
        }

        return false;
    }
}
