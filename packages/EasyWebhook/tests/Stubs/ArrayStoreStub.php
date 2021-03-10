<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Stubs;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyWebhook\Interfaces\Stores\StoreInterface;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Stores\AbstractStore;

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

    public function __construct(RandomGeneratorInterface $random, ?string $id = null)
    {
        $this->id = $id;

        parent::__construct($random);
    }

    public function find(string $id): ?WebhookInterface
    {
        return null;
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
