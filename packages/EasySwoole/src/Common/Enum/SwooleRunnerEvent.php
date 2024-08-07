<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Enum;

enum SwooleRunnerEvent: string
{
    case EnvVarsLoaded = 'envVarsLoaded';
}
