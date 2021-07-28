<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const HTTP_CLIENT = 'easy_webhooks.http_client';

    /**
     * @var string
     */
    public const LOG_CHANNEL = 'webhook';

    /**
     * @var string
     */
    public const PARAM_ASYNC = 'easy_webhooks.async';

    /**
     * @var string
     */
    public const PARAM_BUS = 'easy_webhooks.bus';

    /**
     * @var string
     */
    public const PARAM_EVENT_HEADER = 'easy_webhooks.params.event_header';

    /**
     * @var string
     */
    public const PARAM_ID_HEADER = 'easy_webhooks.params.id_header';

    /**
     * @var string
     */
    public const PARAM_METHOD = 'easy_webhooks.method';

    /**
     * @var string
     */
    public const PARAM_SECRET = 'easy_webhooks.params.secret';

    /**
     * @var string
     */
    public const PARAM_SIGNATURE_HEADER = 'easy_webhooks.params.signature_header';

    /**
     * @var string
     */
    public const SIGNER = 'easy_webhooks.signer';

    /**
     * @var string
     */
    public const STACK = 'easy_webhooks.stack';

    /**
     * @var string
     */
    public const TAG_MIDDLEWARE = 'easy_webhooks.middleware';
}
