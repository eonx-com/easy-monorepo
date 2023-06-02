<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Queue\Configurators;

use EonX\EasyNotification\Interfaces\ConfigInterface;
use EonX\EasyNotification\Interfaces\MessageInterface;
use EonX\EasyNotification\Interfaces\QueueMessageInterface;

final class SignatureConfigurator extends AbstractQueueMessageConfigurator
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
            ->addHeader(QueueMessageInterface::HEADER_SIGNATURE, $signature);
    }
}
