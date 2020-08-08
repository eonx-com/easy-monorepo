<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface QueueMessageConfiguratorInterface
{
    public function configure(
        ConfigInterface $config,
        QueueMessageInterface $queueMessage,
        MessageInterface $message
    ): QueueMessageInterface;

    public function getPriority(): int;
}
