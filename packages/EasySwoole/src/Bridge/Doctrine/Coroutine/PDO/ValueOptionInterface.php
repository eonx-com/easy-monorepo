<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

interface ValueOptionInterface
{
    public const POOL_HEARTBEAT = 'easy_swoole.coroutine_pdo_pool_heartbeat';

    public const POOL_MAX_IDLE_TIME = 'easy_swoole.coroutine_pdo_pool_max_idle_time';

    public const POOL_NAME = 'easy_swoole.coroutine_pdo_pool_name';

    public const POOL_SIZE = 'easy_swoole.coroutine_pdo_pool_size';
}
