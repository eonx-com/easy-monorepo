<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use LaravelDoctrine\ORM\Loggers\Logger;

interface SqlLoggerInterface extends Logger
{
    // Used for container binding purposes.
}
