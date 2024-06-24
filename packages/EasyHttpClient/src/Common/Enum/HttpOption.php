<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Common\Enum;

enum HttpOption: string
{
    case EventsEnabled = 'easy_http_client.events_enabled';

    case RequestDataExtra = 'easy_http_client.request_data_extra';

    case RequestDataModifiers = 'easy_http_client.request_data_modifiers';

    case RequestDataModifiersEnabled = 'easy_http_client.allowed_request_data_enabled';

    case RequestDataModifiersWhitelist = 'easy_http_client.allowed_request_data_whitelist';
}
