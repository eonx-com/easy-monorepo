<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Configurator;

use EonX\EasyNotification\Message\MessageInterface;
use EonX\EasyNotification\Message\QueueMessageInterface;
use EonX\EasyNotification\ValueObject\Config;

final class QueueUrlQueueMessageConfigurator extends AbstractQueueMessageConfigurator
{
    public function configure(
        Config $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message,
    ): QueueMessageInterface {
        return $queueMessage->setQueueUrl($config->getQueueUrl());
    }
}
