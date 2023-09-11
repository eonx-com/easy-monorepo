<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

use Monolog\Processor\ProcessorInterface;

interface ProcessorConfigInterface extends LoggingConfigInterface
{
    public function processor(): ProcessorInterface;
}
