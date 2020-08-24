<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use LaravelDoctrine\ORM\Loggers\Logger;

/**
 * @deprecated since 2.4, will be removed in 3.0. Bugsnag implementation will be reworked.
 */
interface SqlLoggerInterface extends Logger
{
    // Used for container binding purposes.
}
