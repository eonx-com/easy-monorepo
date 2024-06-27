<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Event;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

interface WebhookEventInterface
{
    public function getResult(): WebhookResultInterface;
}
