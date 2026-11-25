<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SnapStart\Hook;

/**
 * Makes unique what the AWS Lambda SnapStart snapshot shares between the restored execution environments, e.g. a random
 * value generated once per process and kept in memory.
 *
 * Runs once per restored execution environment, before its first invocation and after mt_srand(), only in the PHP
 * process restored from the snapshot. A thrown exception is logged as an error and does not fail the invocation.
 * Keep it fast: it delays the first invocation.
 */
interface AfterRestoreHookInterface
{
    public function afterRestore(): void;
}
