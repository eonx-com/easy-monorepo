<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge;

interface BridgeConstantsInterface
{
    public const HTTP_CLIENT = 'easy_webhooks.http_client';

    public const LOG_CHANNEL = 'webhook';

    public const PARAM_ASYNC = 'easy_webhooks.async';

    public const PARAM_BUS = 'easy_webhooks.bus';

    public const PARAM_EVENT_HEADER = 'easy_webhooks.params.event_header';

    public const PARAM_ID_HEADER = 'easy_webhooks.params.id_header';

    public const PARAM_METHOD = 'easy_webhooks.method';

    public const PARAM_SECRET = 'easy_webhooks.params.secret';

    public const PARAM_SIGNATURE_HEADER = 'easy_webhooks.params.signature_header';

    public const SIGNER = 'easy_webhooks.signer';

    public const STACK = 'easy_webhooks.stack';

    public const TAG_MIDDLEWARE = 'easy_webhooks.middleware';
}
