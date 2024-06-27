<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stub\Store;

use EonX\EasyRandom\Generator\RandomGeneratorInterface;
use EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface;
use EonX\EasyWebhook\Common\Cleaner\NullDataCleaner;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Store\AbstractStore;
use EonX\EasyWebhook\Common\Store\StoreInterface;

final class ArrayStoreStub extends AbstractStore implements StoreInterface
{
    /**
     * @var \EonX\EasyWebhook\Common\Entity\WebhookInterface[]
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
