<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Event;

use Swoole\Http\Server;

final readonly class WorkerStartEvent
{
    public function __construct(
        private Server $server,
        private int $workerId,
    ) {
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }
}
