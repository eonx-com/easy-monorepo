<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bundle\Enum;

enum ConfigServiceId: string
{
    case RequestResolver = 'easy_bugsnag.request_resolver';

    case SessionTrackingCache = 'easy_bugsnag.session_tracking.cache';

    case ShutdownStrategy = 'easy_bugsnag.shutdown_strategy';
}
