<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Checker;

use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractAppStateChecker implements AppStateCheckerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
