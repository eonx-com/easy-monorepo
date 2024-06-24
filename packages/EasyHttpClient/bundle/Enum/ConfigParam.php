<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Bundle\Enum;

enum ConfigParam: string
{
    case DecorateDefaultClient = 'easy_http_client.decorate_default_client';

    case DecorateEasyWebhookClient = 'easy_http_client.decorate_easy_webhook_client';

    case DecorateMessengerSqsClient = 'easy_http_client.decorate_messenger_sqs_client';

    case ModifiersEnabled = 'easy_http_client.modifiers.enabled';

    case ModifiersWhitelist = 'easy_http_client.modifiers.whitelist';
}
