<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Messages;

final class RealTimeMessage extends AbstractMessage
{
    public function getType(): string
    {
        return self::TYPE_REAL_TIME;
    }
}
