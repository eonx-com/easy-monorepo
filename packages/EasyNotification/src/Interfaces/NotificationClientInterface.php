<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface NotificationClientInterface
{
    public function send(ConfigInterface $config, MessageInterface $message): void;
}
