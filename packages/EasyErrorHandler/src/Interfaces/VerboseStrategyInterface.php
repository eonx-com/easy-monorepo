<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface VerboseStrategyInterface
{
    public function setThrowable(\Throwable $throwable, ?Request $request = null): self;

    public function isVerbose(): bool;
}
