<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\PushMessage;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\ConfigInterface;
use Nette\Utils\Json;

final class PushBodyQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        if ($message instanceof PushMessage === false) {
            return $queueMessage;
        }

        return $queueMessage->setBody(Json::encode([
            'body' => $message->getBody(),
            'device' => $message->getDevice(),
            'token' => $message->getToken(),
        ]));
    }
}
