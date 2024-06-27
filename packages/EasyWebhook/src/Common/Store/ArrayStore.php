<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use EonX\EasyWebhook\Common\Entity\WebhookInterface;

final class ArrayStore extends AbstractStore implements StoreInterface, ResetStoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Common\Entity\WebhookInterface[]
     */
    private array $webhooks = [];

    public function find(string $id): ?WebhookInterface
    {
        return $this->webhooks[$id] ?? null;
    }

    public function generateWebhookId(): string
    {
        return $this->random->uuid();
    }

    /**
     * @return \EonX\EasyWebhook\Common\Entity\WebhookInterface[]
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
