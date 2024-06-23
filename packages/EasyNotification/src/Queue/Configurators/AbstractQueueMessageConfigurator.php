<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

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
