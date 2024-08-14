<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\Message\RealTimeMessage;
use EonX\EasyNotification\ValueObject\Config;
use Nette\Utils\Json;

final class RealTimeBodyQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        Config $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        if (($message instanceof RealTimeMessage) === false) {
            return $queueMessage;
        }

        return $queueMessage->setBody(Json::encode([
            'body' => $message->getBody(),
            'topics' => $message->getTopics(),
        ]));
    }
}
