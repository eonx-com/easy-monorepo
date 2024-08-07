<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Enum;

enum MessageStatus: string
{
    case OnFly = 'on_fly';

    case Read = 'read';

    case Received = 'received';
}
