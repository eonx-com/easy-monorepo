<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Interfaces;

interface HttpOptionsInterface
{
    public const EVENTS_ENABLED = 'easy_http_client.events_enabled';

    public const REQUEST_DATA_EXTRA = 'easy_http_client.request_data_extra';

    public const REQUEST_DATA_MODIFIERS = 'easy_http_client.request_data_modifiers';

    public const REQUEST_DATA_MODIFIERS_ENABLED = 'easy_http_client.allowed_request_data_enabled';

    public const REQUEST_DATA_MODIFIERS_WHITELIST = 'easy_http_client.allowed_request_data_whitelist';
}
