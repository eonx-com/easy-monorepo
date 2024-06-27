<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Enum;

enum SwooleTableColumnType: int
{
    case Float = 2;

    case Int = 1;

    case String = 3;
}
