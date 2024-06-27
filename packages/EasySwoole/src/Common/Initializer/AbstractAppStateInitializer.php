<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Initializer;

use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractAppStateInitializer implements AppStateInitializerInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
