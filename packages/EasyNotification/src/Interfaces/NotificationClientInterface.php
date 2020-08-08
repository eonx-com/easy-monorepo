<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Interfaces;

interface NotificationClientInterface
{
    public function send(MessageInterface $message): void;
}
