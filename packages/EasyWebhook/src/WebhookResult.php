<?php

declare(strict_types=1);

namespace EonX\EasyWebhook;

use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WebhookResult extends AbstractWebhookResult
{
    // No body needed.
}
