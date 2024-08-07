<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Caching\Enum;

enum CacheTableColumn: string
{
    case Expiry = 'expiry';

    case Value = 'value';
}
