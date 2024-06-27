<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Message;

interface MessageInterface
{
    public const TYPE_FLASH = 'flash';

    public const TYPE_PUSH = 'push';

    public const TYPE_REAL_TIME = 'real_time';

    public const TYPE_SLACK = 'slack';

    public function getBody(): string;

    public function getType(): string;
}
