<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractSecurityContextConfigurator implements SecurityContextConfiguratorInterface
{
    use HasPriorityTrait;

    private bool $propagationStopped = false;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
