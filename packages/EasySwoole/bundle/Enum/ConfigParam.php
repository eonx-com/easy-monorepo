<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\Enum;

enum ConfigParam: string
{
    case AccessLogDoNotLogPaths = 'easy_swoole.access_log_do_not_log_paths';

    case AccessLogTimezone = 'easy_swoole.access_log_timezone';

    case DoctrineCoroutinePdoDefaultHeartbeat = 'easy_swoole.doctrine.coroutine_pdo.default_heartbeat';

    case DoctrineCoroutinePdoDefaultMaxIdleTime = 'easy_swoole.doctrine.coroutine_pdo.default_max_idle_time';

    case DoctrineCoroutinePdoDefaultPoolSize = 'easy_swoole.doctrine.coroutine_pdo.default_pool_size';

    case RequestLimitsMax = 'easy_swoole.request_limits.max';

    case RequestLimitsMin = 'easy_swoole.request_limits.min';

    case ResetDoctrineDbalConnections = 'easy_swoole.reset_doctrine_dbal_connections';

    case ResetEasyBatchProcessor = 'easy_swoole.reset_easy_batch_processor';

    case StaticPhpFilesAllowedDirs = 'easy_swoole.static_php_files_allowed_dirs';

    case StaticPhpFilesAllowedFilenames = 'easy_swoole.static_php_files_allowed_filenames';
}
