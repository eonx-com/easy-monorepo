<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Bundle\Enum;

enum ConfigServiceId: string
{
    case HttpClient = 'easy_webhooks.http_client';

    case Signer = 'easy_webhooks.signer';

    case Stack = 'easy_webhooks.stack';
}
