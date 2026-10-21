<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum ConfigServiceId: string
{
    case JsonFormatter = 'easy_logging.formatter.json';
}
