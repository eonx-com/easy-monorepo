<?php
declare(strict_types=1);

namespace EonX\EasySwoole\AppStateCheckers;

use EonX\EasySwoole\Interfaces\AppStateCheckerInterface;
use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractAppStateChecker implements AppStateCheckerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
