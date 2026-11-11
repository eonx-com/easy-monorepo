<?php
declare(strict_types=1);

namespace EonX\EasyLock\Common\Enum;

/**
 * PSR-3 log levels, the values are the Psr\Log\LogLevel constants.
 */
enum LogLevel: string
{
    case Alert = 'alert';

    case Critical = 'critical';

    case Debug = 'debug';

    case Emergency = 'emergency';

    case Error = 'error';

    case Info = 'info';

    case Notice = 'notice';

    case Warning = 'warning';
}
