<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var int
     */
    public const DEFAULT_CONFIGURATOR_PRIORITY = 5000;

    /**
     * @var string
     */
    public const HTTP_CLIENT = 'easy_webhooks.http_client';

    /**
     * @var string
     */
    public const PARAM_BUS = 'easy_webhooks.bus';

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
    public const TAG_WEBHOOK_CONFIGURATOR = 'easy_webhooks.webhook_configurator';
}
