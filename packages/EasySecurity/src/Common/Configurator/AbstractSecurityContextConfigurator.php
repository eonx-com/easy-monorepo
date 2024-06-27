<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractSecurityContextConfigurator implements SecurityContextConfiguratorInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
