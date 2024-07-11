<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Enum;

enum BugsnagSeverity: string
{
    case Error = 'error';

    case Info = 'info';

    case Warning = 'warning';
}
