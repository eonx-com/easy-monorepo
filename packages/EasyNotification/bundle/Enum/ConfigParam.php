<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bundle\Enum;

enum ConfigParam: string
{
    case ApiUrl = 'easy_notification.param.api_url';

    case ConfigCacheExpiresAfter = 'easy_notification.param.config_cache_expires_after';
}
