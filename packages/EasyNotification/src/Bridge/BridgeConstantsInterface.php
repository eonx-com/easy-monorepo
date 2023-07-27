<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bridge;

interface BridgeConstantsInterface
{
    public const CONFIG_CACHE_EXPIRES_AFTER = 3600;

    public const CONFIG_CACHE_KEY = 'easy_notification.config';

    public const EXTENSION_NAME = 'easy_notification';

    public const PARAM_API_URL = 'easy_notification.param.api_url';

    public const PARAM_CONFIG_CACHE_EXPIRES_AFTER = 'easy_notification.param.config_cache_expires_after';

    public const SERVICE_CONFIG_CACHE = 'easy_notification.config_cache';

    public const TAG_QUEUE_MESSAGE_CONFIGURATOR = 'easy_notification.queue_message_configurator';
}
