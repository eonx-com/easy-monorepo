<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle\Enum;

enum ConfigServiceId: string
{
    case Connection = 'easy_lock.connection';

    case Store = 'easy_lock.store';
}
