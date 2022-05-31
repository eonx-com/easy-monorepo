<?php

declare(strict_types=1);

namespace EonX\EasySwoole;

use EonX\EasySwoole\Interfaces\ApplicationStateCheckerInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractApplicationStateChecker implements ApplicationStateCheckerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
