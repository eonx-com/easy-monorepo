<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bundle\Enum;

enum ConfigTag: string
{
    case AppStateChecker = 'easy_swoole.app_state_checker';

    case AppStateInitializer = 'easy_swoole.app_state_initializer';

    case AppStateResetter = 'easy_swoole.app_state_resetter';
}
