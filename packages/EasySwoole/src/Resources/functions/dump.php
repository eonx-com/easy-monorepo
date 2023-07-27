<?php
declare(strict_types=1);

use EonX\EasySwoole\Exceptions\SwooleDdException;
use EonX\EasySwoole\Helpers\VarDumpHelper;

if (\function_exists('swoole_dump') === false) {
    function swoole_dump(...$vars): void
    {
        foreach ($vars as $var) {
            echo VarDumpHelper::dump($var);
        }
    }
}

if (\function_exists('swoole_dd') === false) {
    function swoole_dd(...$vars): never
    {
        swoole_dump($vars);

        throw new SwooleDdException('swoole_dd');
    }
}
