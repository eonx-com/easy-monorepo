<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Bundle\Enum;

enum BundleParam: string
{
    case BugsnagHandlerName = 'easy_logging_bugsnag';

    case KeyChannel = 'easy_logging_channel';
}
