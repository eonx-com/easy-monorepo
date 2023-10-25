<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Interfaces;

interface RequestAttributesInterface
{
    public const EASY_SWOOLE_APP_STATE_COMPROMISED = 'easy_swoole_app_state_compromised';

    public const EASY_SWOOLE_ENABLED = 'easy_swoole_enabled';

    public const EASY_SWOOLE_REQUEST_START_TIME = 'easy_swoole_request_start_time';

    public const EASY_SWOOLE_WORKER_ID = 'easy_swoole_worker_id';
}
