<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

use Monolog\Logger;

interface LoggerConfiguratorInterface extends LoggingConfigInterface
{
    public function configure(Logger $logger): void;
}
