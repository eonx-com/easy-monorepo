<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Enum;

use EonX\EasyUtils\Common\Enum\EnumTrait;

enum SwooleServerEvent: string
{
    use EnumTrait;

    case AfterReload = 'afterReload';

    case BeforeReload = 'beforeReload';

    case Close = 'close';

    case Connect = 'connect';

    case Finish = 'finish';

    case ManagerStart = 'managerStart';

    case ManagerStop = 'managerStop';

    case PipeMessage = 'pipeMessage';

    case Receive = 'receive';

    case Request = 'request';

    case Shutdown = 'shutdown';

    case Task = 'task';

    case WorkerError = 'workerError';

    case WorkerExit = 'workerExit';

    case WorkerStart = 'workerStart';

    case WorkerStop = 'workerStop';
}
