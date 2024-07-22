<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyErrorHandler/src/Bridge/EasyBugsnag/UnhandledCallbackBridge.php
namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag;
========
namespace EonX\EasyErrorHandler\Bugsnag\Helper;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/src/Bugsnag/Helper/UnhandledCallbackBridgeHelper.php

use Bugsnag\Report;
use Closure;

final class UnhandledCallbackBridgeHelper
{
    private readonly Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function __invoke(Report $report, callable $next): void
    {
        if (\call_user_func($this->callback, $report) === false) {
            return;
        }

        $next($report);
    }
}
