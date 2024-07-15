<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Enum;

enum ActivityAction: string
{
    case Create = 'create';

    case Delete = 'delete';

    case Update = 'update';
}
