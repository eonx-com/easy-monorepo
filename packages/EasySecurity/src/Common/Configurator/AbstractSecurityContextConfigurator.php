<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;
use EonX\EasyUtils\Common\Helper\StoppableTrait;

abstract class AbstractSecurityContextConfigurator implements SecurityContextConfiguratorInterface
{
    use HasPriorityTrait;
    use StoppableTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }
}
