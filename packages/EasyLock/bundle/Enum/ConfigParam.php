<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bundle\Enum;

enum ConfigParam: string
{
    case Connection = 'easy_lock.param.connection';

    case MessengerMiddlewareAutoRegister = 'easy_lock.messenger_middleware_auto_register';
}
