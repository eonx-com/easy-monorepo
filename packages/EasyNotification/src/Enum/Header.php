<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Enum;

enum Header: string
{
    case Provider = 'provider';

    case Signature = 'signature';

    case Type = 'type';
}
