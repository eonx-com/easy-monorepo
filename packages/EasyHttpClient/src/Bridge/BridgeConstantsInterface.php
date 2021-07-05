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
}
