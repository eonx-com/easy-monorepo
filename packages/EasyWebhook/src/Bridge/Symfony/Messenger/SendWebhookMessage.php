<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;
use EonX\EasyWebhook\Interfaces\WebhookResultInterface;

final class SendWebhookMessage implements WithLockDataInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookResultInterface
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

    public function getLockData(): LockDataInterface
    {
        return LockData::create(\sprintf('easy_webhook_send_%s', $this->webhookId));
    }

    public function getResult(): ?WebhookResultInterface
    {
        return $this->result;
    }

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }

    public function setResult(WebhookResultInterface $result): self
    {
        $this->result = $result;

        return $this;
    }
}
