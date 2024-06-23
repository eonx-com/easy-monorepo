<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface QueueMessageConfiguratorInterface extends HasPriorityInterface
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface;
}
