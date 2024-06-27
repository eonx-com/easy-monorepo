<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bundle\Enum;

enum ConfigParam: string
{
    case Async = 'easy_webhooks.async';

    case Bus = 'easy_webhooks.bus';

    case EventHeader = 'easy_webhooks.event_header';

    case IdHeader = 'easy_webhooks.id_header';

    case Method = 'easy_webhooks.method';

    case Secret = 'easy_webhooks.secret';

    case SignatureHeader = 'easy_webhooks.signature_header';
}
