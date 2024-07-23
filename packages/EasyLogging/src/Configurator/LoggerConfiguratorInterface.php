<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Configurator;

use EonX\EasyLogging\Config\LoggingConfigInterface;
use Monolog\Logger;

interface LoggerConfiguratorInterface extends LoggingConfigInterface
{
    public function configure(Logger $logger): void;
}
