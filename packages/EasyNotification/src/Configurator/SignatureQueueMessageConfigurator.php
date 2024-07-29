<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Enum\MessageHeader;
use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\ConfigInterface;

final class SignatureQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        $body = \trim($queueMessage->getBody());

        if ($body === '') {
            return $queueMessage;
        }

        $signature = \hash_hmac($config->getAlgorithm(), $body, $config->getSecret());

        return $queueMessage
            ->setBody($body)
            ->addHeader(MessageHeader::Signature->value, $signature);
    }
}
