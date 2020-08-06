<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\QueueMessageConfiguratorInterface;

abstract class AbstractQueueMessageConfigurator implements QueueMessageConfiguratorInterface
{
    /**
     * @var int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority ?? 0;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
