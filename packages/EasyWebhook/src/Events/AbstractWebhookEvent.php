<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Events;

use EonX\EasyWebhook\Interfaces\WebhookEventInterface;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

abstract class AbstractWebhookEvent implements WebhookEventInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface
     */
    private $result;

    public function __construct(WebhookResultInterface $result)
    {
        $this->result = $result;
    }

    public function getResult(): WebhookResultInterface
    {
        return $this->result;
    }
}
