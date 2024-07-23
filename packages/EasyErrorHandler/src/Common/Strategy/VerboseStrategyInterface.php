<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Strategy;

use Symfony\Component\HttpFoundation\Request;
use Throwable;

interface VerboseStrategyInterface
{
    public function isVerbose(): bool;

    public function setThrowable(Throwable $throwable, ?Request $request = null): self;
}
