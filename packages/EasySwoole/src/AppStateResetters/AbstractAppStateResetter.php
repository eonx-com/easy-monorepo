<?php
declare(strict_types=1);

namespace EonX\EasySwoole\AppStateResetters;

use EonX\EasySwoole\Interfaces\AppStateResetterInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractAppStateResetter implements AppStateResetterInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
