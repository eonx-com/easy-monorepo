<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Enum\MessageHeader;
use EonX\EasyNotification\Message\IdempotencyKeyAwareMessageInterface;
use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\Config;

final class IdempotencyKeyQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        Config $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        if ($message instanceof IdempotencyKeyAwareMessageInterface === false) {
            return $queueMessage;
        }

        $idempotencyKey = $message->getIdempotencyKey();

        if ($idempotencyKey === null) {
            return $queueMessage;
        }

        return $queueMessage->addHeader(MessageHeader::IdempotencyKey->value, $idempotencyKey);
    }
}
