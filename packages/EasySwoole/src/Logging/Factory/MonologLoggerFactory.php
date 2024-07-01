<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Logging\Factory;

use DateTimeZone;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final readonly class MonologLoggerFactory
{
    public function __construct(
        private string $timezone = 'UTC',
    ) {
    }

    public function create(): LoggerInterface
    {
        return new Logger('swoole', [new StreamHandler('php://stdout')], [], new DateTimeZone($this->timezone));
    }
}
