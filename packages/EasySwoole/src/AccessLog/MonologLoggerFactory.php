<?php

declare(strict_types=1);

namespace EonX\EasySwoole\AccessLog;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class MonologLoggerFactory
{
    public function __construct(
        private readonly string $timezone = 'UTC',
    ) {
    }

    public function create(): LoggerInterface
    {
        return new Logger('swoole', [new StreamHandler('php://stdout')], [], new \DateTimeZone($this->timezone));
    }
}
