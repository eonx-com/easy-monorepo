<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Enum;

enum MessageHeader: string
{
    case IdempotencyKey = 'idempotencyKey';

    case Provider = 'provider';

    case Signature = 'signature';

    case Type = 'type';
}
