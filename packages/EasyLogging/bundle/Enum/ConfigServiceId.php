<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum ConfigServiceId: string
{
    case BugsnagMonologHandlerFormatter = 'easy_logging.bugsnag_monolog_handler.formatter';
}
