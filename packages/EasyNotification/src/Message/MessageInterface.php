<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\MessageType;

interface MessageInterface
{
    public function getBody(): string;

    public function getType(): MessageType;
}
