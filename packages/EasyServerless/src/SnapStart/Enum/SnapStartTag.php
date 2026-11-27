<?php
declare(strict_types=1);

namespace EonX\EasyServerless\SnapStart\Enum;

enum SnapStartTag: string
{
    case AfterRestoreHook = 'easy_serverless.snap_start_after_restore_hook';

    case Warmer = 'easy_serverless.snap_start_warmer';
}
