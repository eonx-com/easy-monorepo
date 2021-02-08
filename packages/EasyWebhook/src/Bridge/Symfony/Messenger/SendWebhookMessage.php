<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class SendWebhookMessage
{
    /**
     * @var null|\EonX\EasyWebhook\Interfaces\WebhookResultInterface
     */
    private $result;

    /**
     * @var string
     */
    private $webhookId;

    public function __construct(string $webhookId)
    {
        $this->webhookId = $webhookId;
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
