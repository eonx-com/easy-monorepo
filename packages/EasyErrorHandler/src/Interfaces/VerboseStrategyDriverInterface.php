<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

interface VerboseStrategyDriverInterface extends HasPriorityInterface
{
    public function isVerbose(Throwable $throwable, ?Request $request = null): ?bool;
}
