<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const LOG_CHANNEL = 'http_client';

    /**
     * @var string
     */
    public const PARAM_DECORATE_DEFAULT_CLIENT = 'easy_http_client.decorate_default_client';

    /**
     * @var string
     */
    public const PARAM_DECORATE_EASY_WEBHOOK_CLIENT = 'easy_http_client.decorate_easy_webhook_client';

    /**
     * @var string
     */
    public const PARAM_MODIFIERS_ENABLED = 'easy_http_client.modifiers.enabled';

    /**
     * @var string
     */
    public const PARAM_MODIFIERS_WHITELIST = 'easy_http_client.modifiers.whitelist';

    /**
     * @var string
     */
    public const SERVICE_HTTP_CLIENT = 'easy_http_client.http_client';

    /**
     * @var string
     */
    public const SERVICE_LOGGER = 'easy_http_client.logger';

    /**
     * @var string
     */
    public const TAG_REQUEST_DATA_MODIFIER = 'easy_http_client.request_data_modifier';
}
