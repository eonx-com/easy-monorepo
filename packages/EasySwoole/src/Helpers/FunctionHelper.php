<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use OpenSwoole\Util;

final class FunctionHelper
{
    public static function countCpu(): int
    {
        if (\function_exists('swoole_cpu_num')) {
            return \swoole_cpu_num();
        }

        if (\class_exists(Util::class) && \method_exists(Util::class, 'getCPUNum')) {
            return Util::getCPUNum();
        }

        return 1;
    }
}
