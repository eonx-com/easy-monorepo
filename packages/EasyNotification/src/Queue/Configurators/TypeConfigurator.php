<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;

final class TypeConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        return $queueMessage->addHeader(QueueMessageInterface::HEADER_TYPE, $message->getType());
    }
}
