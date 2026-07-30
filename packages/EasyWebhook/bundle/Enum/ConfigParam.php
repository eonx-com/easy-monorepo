<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bundle\Enum;

enum ConfigParam: string
{
    case AsyncEnabled = 'easy_webhooks.async_enabled';

    case Bus = 'easy_webhooks.bus';

    case EventHeader = 'easy_webhooks.event_header';

    case IdHeader = 'easy_webhooks.id_header';

    case Method = 'easy_webhooks.method';

    case RequestLimitsEnabled = 'easy_webhooks.request_limits_enabled';

    case RequestMaxDuration = 'easy_webhooks.request_max_duration';

    case RequestMaxResponseBytes = 'easy_webhooks.request_max_response_bytes';

    case RequestTimeout = 'easy_webhooks.request_timeout';

    case Secret = 'easy_webhooks.secret';

    case SignatureHeader = 'easy_webhooks.signature_header';
}
