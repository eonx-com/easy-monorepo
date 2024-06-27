<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Client;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

interface WebhookClientInterface
{
    public function sendWebhook(WebhookInterface $webhook): WebhookResultInterface;
}
