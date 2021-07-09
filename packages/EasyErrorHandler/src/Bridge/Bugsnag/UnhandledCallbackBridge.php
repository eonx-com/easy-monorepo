<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Bugsnag;

use Bugsnag\Report;

final class UnhandledCallbackBridge
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(Report $report, callable $next): void
    {
        if (\call_user_func($this->callback, $report) === false) {
            return;
        }

        $next($report);
    }
}
