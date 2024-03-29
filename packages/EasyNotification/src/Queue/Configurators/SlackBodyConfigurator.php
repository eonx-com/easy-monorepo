<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;
use EonX\EasyNotification\Messages\SlackMessage;

final class SlackBodyConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        if (($message instanceof SlackMessage) === false) {
            return $queueMessage;
        }

        return $queueMessage->setBody($message->getBody());
    }
}
