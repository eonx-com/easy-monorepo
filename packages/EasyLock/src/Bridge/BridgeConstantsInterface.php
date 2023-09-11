<?php
declare(strict_types=1);

namespace EonX\EasyLock\Bridge;

interface BridgeConstantsInterface
{
    public const LOG_CHANNEL = 'lock';

    public const PARAM_CONNECTION = 'easy_lock.param.connection';

    public const PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER = 'easy_lock.messenger_middleware_auto_register';

    public const SERVICE_CONNECTION = 'easy_lock.connection';

    public const SERVICE_STORE = 'easy_lock.store';
}
