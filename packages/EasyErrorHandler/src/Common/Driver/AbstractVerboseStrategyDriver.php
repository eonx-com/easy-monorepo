<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Driver;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractVerboseStrategyDriver implements VerboseStrategyDriverInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
