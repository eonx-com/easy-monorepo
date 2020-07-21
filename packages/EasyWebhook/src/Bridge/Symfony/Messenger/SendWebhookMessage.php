<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Bridge\Symfony\Messenger;

use EonX\EasyLock\Interfaces\LockDataInterface;
use EonX\EasyLock\Interfaces\WithLockDataInterface;
use EonX\EasyLock\LockData;

final class SendWebhookMessage implements WithLockDataInterface
{
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

    public function getWebhookId(): string
    {
        return $this->webhookId;
    }
}
