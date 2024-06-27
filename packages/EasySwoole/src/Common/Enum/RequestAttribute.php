<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Enum;

enum RequestAttribute: string
{
    case EasySwooleAppStateCompromised = 'easy_swoole_app_state_compromised';

    case EasySwooleEnabled = 'easy_swoole_enabled';

    case EasySwooleRequestStartTime = 'easy_swoole_request_start_time';

    case EasySwooleWorkerId = 'easy_swoole_worker_id';
}
