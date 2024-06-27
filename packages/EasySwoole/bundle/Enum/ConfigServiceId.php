<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\Enum;

enum ConfigServiceId: string
{
    case AccessLogLogger = 'easy_swoole.access_log_logger';

    case Filesystem = 'easy_swoole.filesystem';
}
