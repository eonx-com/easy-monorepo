<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Throwable;

interface IgnoreExceptionsResolverInterface
{
    public function shouldIgnore(Throwable $throwable): bool;
}
