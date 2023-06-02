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
     * @var null|string
     */
    private $id;

    /**
     * @var \EonX\EasyWebhook\Interfaces\WebhookInterface[]
     */
    private $webhooks = [];

    public function __construct(
        RandomGeneratorInterface $random,
        ?string $id = null,
        ?DataCleanerInterface $dataCleaner = null
    ) {
        $this->id = $id;

        parent::__construct($random, $dataCleaner ?? new NullDataCleaner());
    }

    public function find(string $id): ?WebhookInterface
    {
        return $this->webhooks[$id] ?? null;
    }

    public function generateWebhookId(): string
    {
        return $this->id ?? $this->random->uuidV4();
    }

    public function store(WebhookInterface $webhook): WebhookInterface
    {
        if ($this->id !== null) {
            $webhook->id($this->generateWebhookId());
        }

        return $this->webhooks[$webhook->getId()] = $webhook;
    }
}
