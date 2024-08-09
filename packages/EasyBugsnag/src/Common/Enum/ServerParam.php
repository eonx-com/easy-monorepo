<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Enum;

enum ServerParam: string
{
    case ResolveRequestInCli = 'easy_bugsnag.resolve_request_in_cli';
}
