<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Event;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

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
