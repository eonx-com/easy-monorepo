<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Enum;

enum ExceptionSeverity: string
{
    case Error = 'error';

    case Info = 'info';

    case Warning = 'warning';
}
