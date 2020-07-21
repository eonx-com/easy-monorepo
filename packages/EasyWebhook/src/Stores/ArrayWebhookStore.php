<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Interfaces\WebhookStoreInterface;
use EonX\EasyWebhook\Webhook;

final class ArrayWebhookStore implements WebhookStoreInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    /**
     * @var mixed[]
     */
    private $webhooks = [];

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function find(string $webhookId): ?WebhookInterface
    {
        $webhook = $this->webhooks[$webhookId] ?? null;

        if ($webhook === null) {
            return null;
        }

        $class = $webhook['class'] ?? Webhook::class;

        return $class::fromArray($webhook)->setId($webhookId);
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function reset(): void
    {
        $this->webhooks = [];
    }

    /**
     * @param mixed[] $data
     */
    public function store(array $data, ?string $id = null): string
    {
        $id = $id ?? $this->random->uuidV4();

        $this->webhooks[$id] = $data;

        return $id;
    }
}
