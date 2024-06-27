<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyUtils\Traits\HasPriorityTrait;

abstract class AbstractQueueMessageConfigurator implements QueueMessageConfiguratorInterface
{
    use HasPriorityTrait;

    public function __construct(?int $priority = null)
    {
        $this->doSetPriority($priority);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
