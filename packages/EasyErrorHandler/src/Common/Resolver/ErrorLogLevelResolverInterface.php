<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Resolver;

use Throwable;

interface ErrorLogLevelResolverInterface
{
    public function getLogLevel(Throwable $throwable): int;
}
