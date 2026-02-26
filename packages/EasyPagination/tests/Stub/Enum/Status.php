<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Stub\Enum;

enum Status: string
{
    case Active = 'active';

    case Inactive = 'inactive';
}
