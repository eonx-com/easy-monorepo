<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var int
     */
    public const CONFIG_CACHE_EXPIRES_AFTER = 3600;

    /**
     * @var string
     */
    public const CONFIG_CACHE_KEY = 'easy_notification.config';

    /**
     * @var string
     */
    public const EXTENSION_NAME = 'easy_notification';

    /**
     * @var string
     */
    public const PARAM_CONFIG_CACHE_EXPIRES_AFTER = 'easy_notification.param.config_cache_expires_after';

    /**
     * @var string
     */
    public const SERVICE_CONFIG_CACHE = 'easy_notification.config_cache';

    /**
     * @var string
     */
    public const SERVICE_SQS_CLIENT = 'easy_notification.sqs_client';

    /**
     * @var string
     */
    public const TAG_QUEUE_MESSAGE_CONFIGURATOR = 'easy_notification.queue_message_configurator';
}
