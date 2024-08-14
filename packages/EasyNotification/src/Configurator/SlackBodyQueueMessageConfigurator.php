<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\Message\SlackMessage;
use EonX\EasyNotification\ValueObject\Config;

final class SlackBodyQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        Config $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        if (($message instanceof SlackMessage) === false) {
            return $queueMessage;
        }

        return $queueMessage->setBody($message->getBody());
    }
}
