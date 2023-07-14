<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Events;

use EonX\EasyWebhook\Interfaces\WebhookEventInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

abstract class AbstractWebhookEvent implements WebhookEventInterface
{
    public function __construct(
        private WebhookResultInterface $result,
    ) {
    }

    public function getResult(): WebhookResultInterface
    {
        return $this->result;
    }
}
