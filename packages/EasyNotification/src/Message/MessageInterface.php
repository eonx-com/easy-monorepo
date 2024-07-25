<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

use EonX\EasyNotification\Enum\Type;

interface MessageInterface
{
    public function getBody(): string;

    public function getType(): Type;
}
