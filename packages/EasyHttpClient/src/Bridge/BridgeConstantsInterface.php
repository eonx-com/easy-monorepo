<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bridge;

interface BridgeConstantsInterface
{
    public const LOG_CHANNEL = 'easy_http_client';

    public const PARAM_DECORATE_DEFAULT_CLIENT = 'easy_http_client.decorate_default_client';

    public const PARAM_DECORATE_EASY_WEBHOOK_CLIENT = 'easy_http_client.decorate_easy_webhook_client';

    public const PARAM_DECORATE_MESSENGER_SQS_CLIENT = 'easy_http_client.decorate_messenger_sqs_client';

    public const PARAM_MODIFIERS_ENABLED = 'easy_http_client.modifiers.enabled';

    public const PARAM_MODIFIERS_WHITELIST = 'easy_http_client.modifiers.whitelist';

    public const SERVICE_HTTP_CLIENT = 'easy_http_client.http_client';

    public const SERVICE_LOGGER = 'easy_http_client.logger';

    public const TAG_REQUEST_DATA_MODIFIER = 'easy_http_client.request_data_modifier';
}
