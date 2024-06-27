<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Messenger\Message;

use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;

final class SendWebhookMessage
{
    private ?WebhookResultInterface $result = null;

    public function __construct(
        private string $webhookId,
    ) {
    }

    public function getResult(): ?WebhookResultInterface
    {
        return $this->result;
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    public function setResult(?WebhookResultInterface $result = null): self
    {
        $this->result = $result;

        return $this;
    }
}
