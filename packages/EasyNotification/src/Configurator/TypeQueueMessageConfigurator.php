<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\ConfigInterface;

final class TypeQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        return $queueMessage->addHeader(QueueMessageInterface::HEADER_TYPE, $message->getType());
    }
}