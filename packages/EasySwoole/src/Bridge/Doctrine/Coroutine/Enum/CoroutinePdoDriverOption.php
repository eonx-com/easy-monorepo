<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\Enum;

enum CoroutinePdoDriverOption: string
{
    case PoolHeartbeat = 'easy_swoole.doctrine.coroutine_pdo.pool_heartbeat';

    case PoolMaxIdleTime = 'easy_swoole.doctrine.coroutine_pdo.pool_max_idle_time';

    case PoolName = 'easy_swoole.doctrine.coroutine_pdo.pool_name';

    case PoolSize = 'easy_swoole.doctrine.coroutine_pdo.pool_size';
}
