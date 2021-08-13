<?php

declare(strict_types=1);

namespace EonX\EasyHttpClient\Interfaces;

interface HttpOptionsInterface
{
    /**
     * @var string
     */
    public const REQUEST_DATA_MODIFIERS = 'easy_http_client.request_data_modifiers';

    /**
     * @var string
     */
    public const REQUEST_DATA_MODIFIERS_ENABLED = 'easy_http_client.allowed_request_data_enabled';

    /**
     * @var string
     */
    public const REQUEST_DATA_MODIFIERS_WHITELIST = 'easy_http_client.allowed_request_data_whitelist';

    /**
     * @var string
     */
    public const REQUEST_DATA_SUBSCRIBERS = 'easy_http_client.request_data_subscribers';
}
