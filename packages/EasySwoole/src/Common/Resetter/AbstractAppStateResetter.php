<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Resetter;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractAppStateResetter implements AppStateResetterInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
