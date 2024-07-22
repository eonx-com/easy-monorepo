<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use Monolog\Handler\HandlerInterface;

interface HandlerConfigInterface extends LoggingConfigInterface
{
    public function handler(): HandlerInterface;
}
