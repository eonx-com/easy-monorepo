<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\ResetStoreInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;

final class ArrayStore extends AbstractStore implements StoreInterface, ResetStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    private $webhooks = [];

    public function find(string $id): ?WebhookInterface
    {
        return $this->webhooks[$id] ?? null;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuidV4();
    }

    /**
     * @return \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function reset(): void
    {
        $this->webhooks = [];
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        if ($webhook->getId() === null) {
            $webhook->id($this->generateWebhookId());
        }

        return $this->webhooks[$webhook->getId()] = $webhook;
    }
}
