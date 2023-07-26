<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge;

interface BridgeConstantsInterface
{
    public const LOG_CHANNEL = 'async';

    public const PARAM_MESSENGER_MIDDLEWARE_AUTO_REGISTER = 'easy_async.messenger_middleware_auto_register';

    public const PARAM_MESSENGER_WORKER_STOP_MIN_MESSAGES = 'easy_async.messenger_worker_stop_min_messages';

    public const PARAM_MESSENGER_WORKER_STOP_MAX_MESSAGES = 'easy_async.messenger_worker_stop_max_messages';

    public const PARAM_MESSENGER_WORKER_STOP_MIN_TIME = 'easy_async.messenger_worker_stop_min_time';

    public const PARAM_MESSENGER_WORKER_STOP_MAX_TIME = 'easy_async.messenger_worker_stop_max_time';

    public const SERVICE_LOGGER = 'easy_async.logger';
}
