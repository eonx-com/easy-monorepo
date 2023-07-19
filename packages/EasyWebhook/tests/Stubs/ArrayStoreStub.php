<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\AbstractStore;
use EonX\EasyWebhook\Stores\NullDataCleaner;

final class ArrayStoreStub extends AbstractStore implements StoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    private array $webhooks = [];

    public function __construct(
        RandomGeneratorInterface $random,
        private ?string $id = null,
        ?DataCleanerInterface $dataCleaner = null,
    ) {
        parent::__construct($random, $dataCleaner ?? new NullDataCleaner());
    }

    public function find(string $id): ?WebhookInterface
    {
        return $this->webhooks[$id] ?? null;
    }

    public function generateWebhookId(): string
    {
        return $this->id ?? $this->random->uuid();
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        if ($this->id !== null) {
            $webhook->id($this->generateWebhookId());
        }

        return $this->webhooks[$webhook->getId()] = $webhook;
    }
}
