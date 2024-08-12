<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle\Enum;

enum ConfigParam: string
{
    case Connection = 'easy_lock.param.connection';

    case MessengerMiddlewareEnabled = 'easy_lock.messenger_middleware_enabled';
}
