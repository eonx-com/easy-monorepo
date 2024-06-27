<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\ConfigInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface QueueMessageConfiguratorInterface extends HasPriorityInterface
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface;
}
