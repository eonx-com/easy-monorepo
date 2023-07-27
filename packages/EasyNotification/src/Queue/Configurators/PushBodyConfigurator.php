<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;
use EonX\EasyNotification\Messages\PushMessage;
use Nette\Utils\Json;

final class PushBodyConfigurator extends AbstractQueueMessageConfigurator
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
