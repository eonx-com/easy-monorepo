<?php

declare(strict_types=1);

namespace EonX\EasySwoole;

use EonX\EasySwoole\Interfaces\ApplicationStateResetterInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractApplicationStateResetter implements ApplicationStateResetterInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
